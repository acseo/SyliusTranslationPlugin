<?php
declare(strict_types=1);

namespace Yaroslavche\SyliusTranslationPlugin\Service;

use DateTime;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Safe\Exceptions\StringsException;
use SplFileInfo;
use Sylius\Component\Locale\Model\Locale;
use Sylius\Component\Locale\Provider\LocaleProviderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Translation\Dumper\XliffFileDumper;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Writer\TranslationWriter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function \sprintf;

class TranslationService
{

    const CACHE_POOL_TAG = 'yaroslavche_sylius_translation_plugin';
    const FULL_MESSAGE_CATALOGUE_CACHE_KEY = 'full_message_catalogue';
    const LOCALE_TRANSLATED_MESSAGE_CATALOGUE_CACHE_KEY = 'translated_message_catalogue_%s';
    const LOCALE_CUSTOM_MESSAGE_CATALOGUE_CACHE_KEY = 'custom_message_catalogue_%s';

    /** @var TranslatorInterface|DataCollectorTranslator $translator */
    private $translator;

    /** @var RepositoryInterface $localeRepository */
    private $localeRepository;

    /** @var LocaleProviderInterface $localeProvider */
    private $localeProvider;

    /** @var string $kernelRootDir */
    private $kernelRootDir;

    /** @var Filesystem $filesystem */
    private $filesystem;

    /** @var Finder $finder */
    private $finder;

    /** @var FilesystemAdapter $cache */
    private $cache;

    /** @var array $translatorPaths */
    private $translatorPaths;

    /** @var string $translatorDefaultPath */
    private $translatorDefaultPath;

    /** @var CacheClearerInterface $cacheClearer */
    private $cacheClearer;

    /** @var string $kernelTranslationsCacheDir */
    private $kernelTranslationsCacheDir;

    /** @var CacheWarmerInterface $warmer */
    private CacheWarmerInterface $warmer;

    /**
     * TranslationService constructor.
     * @param TranslatorInterface|DataCollectorTranslator $translator
     * @param RepositoryInterface $localeRepository
     * @param LocaleProviderInterface $localeProvider
     * @param string $kernelRootDir
     * @param array $translatorPaths
     * @param string $translatorDefaultPath
     * @param CacheClearerInterface $cacheClearer
     * @param string $kernelTranslationsCacheDir
     * @param CacheWarmerInterface $warmer
     */
    public function __construct(
        TranslatorInterface $translator,
        RepositoryInterface $localeRepository,
        LocaleProviderInterface $localeProvider,
        string $kernelRootDir,
        array $translatorPaths,
        string $translatorDefaultPath,
        CacheClearerInterface $cacheClearer,
        string $kernelTranslationsCacheDir,
        CacheWarmerInterface $warmer
    )
    {
        $this->translator = $translator;
        $this->localeRepository = $localeRepository;
        $this->localeProvider = $localeProvider;
        $this->kernelRootDir = $kernelRootDir;
        $this->translatorPaths = $translatorPaths;
        $this->translatorDefaultPath = $translatorDefaultPath;
        $this->cacheClearer = $cacheClearer;
        $this->kernelTranslationsCacheDir = $kernelTranslationsCacheDir;
        $this->warmer = $warmer;
        $this->filesystem = new Filesystem();
        $this->finder = new Finder();
        $this->cache = new FilesystemAdapter(static::CACHE_POOL_TAG);
    }

    /**
     * @return string
     */
    public function getDefaultLocaleCode(): string
    {
        return $this->localeProvider->getDefaultLocaleCode();
    }

    /**
     * @return string[] $localeCode => $localeName
     */
    public function getSupportedLocales(): array
    {
        return Locales::getNames($this->getDefaultLocaleCode());
    }

    /**
     * @return string[]
     */
    public function getAvailableLocales(): array
    {
        $availableLocales = [];
        $syliusLocales = $this->localeRepository->findAll();
        /** @var Locale $locale */
        foreach ($syliusLocales as $key => $locale) {
            $availableLocales[$locale->getCode()] = $locale->getName();
        }
        return $availableLocales;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * @param bool $forceRevalidate
     * @return MessageCatalogue
     * @throws InvalidArgumentException
     */
    public function getFullMessageCatalogue(bool $forceRevalidate = false): MessageCatalogue
    {
        $locales = Locales::getLocales();

        if ($forceRevalidate) {
            $this->cache->delete(static::FULL_MESSAGE_CATALOGUE_CACHE_KEY);
        }

        /** @var MessageCatalogue $catalogue */
        $catalogue = $this->cache->get(
            static::FULL_MESSAGE_CATALOGUE_CACHE_KEY,
            function (ItemInterface $item) use ($locales) {
                $catalogue = new MessageCatalogue($this->getDefaultLocaleCode());
                $availableLocales = $this->getAvailableLocales();
                $localeCode = $this->translator->getLocale();

                foreach ($locales as $key => $currentLocaleCode) {
                    $this->translator->setLocale($currentLocaleCode);
                    $localeMessageCatalogue = $this->translator->getCatalogue($currentLocaleCode);
                    foreach ($localeMessageCatalogue->all() as $domain => $translations) {
                        foreach ($translations as $id => $translation) {
                            if (!$catalogue->has($id, $domain)) {
                                $catalogue->set($id, '', $domain);
                            }
                        }
                    }

                    if (array_key_exists($currentLocaleCode, $availableLocales)) {
                        $localeCustomMessageCatalogue = $this->getCustomMessageCatalogue($currentLocaleCode);
                        foreach ($localeCustomMessageCatalogue->all() as $domain => $translations) {
                            foreach ($translations as $id => $translation) {
                                if (!$catalogue->has($id, $domain)) {
                                    $catalogue->set($id, '', $domain);
                                }
                            }
                        }
                    }
                }
                $this->translator->setLocale($localeCode);

                return $catalogue;
            }
        );
        return $catalogue;
    }

    /**
     * @param string $localeCode
     * @return MessageCatalogue
     * @throws StringsException
     */
    public function getTranslatedMessageCatalogue(string $localeCode): MessageCatalogue
    {
        $locales = Locales::getLocales();
        if (!in_array($localeCode, $locales)) {
            throw new Exception(sprintf('Invalid locale code "%s"', $localeCode));
        }

        $previousLocaleCode = $this->translator->getLocale();
        $this->translator->setLocale($localeCode);
        $catalogue = $this->translator->getCatalogue($localeCode);
        $this->translator->setLocale($previousLocaleCode);
        return $catalogue;
    }

    /**
     * @param string $localeCode
     * @return MessageCatalogue
     * @throws StringsException
     */
    public function getCustomMessageCatalogue(string $localeCode): MessageCatalogue
    {
        $locales = Locales::getLocales();
        if (!in_array($localeCode, $locales)) {
            throw new Exception(sprintf('Invalid locale code "%s"', $localeCode));
        }

        $catalogue = new MessageCatalogue($localeCode);

        foreach ($this->translatorPaths as $path){
            $this->feedCatalogue(realpath($path), $localeCode, $catalogue);
        }

        $this->feedCatalogue(realpath($this->translatorDefaultPath), $localeCode, $catalogue);

        return $catalogue;
    }

    /**
     * @param string $localeCode
     * @param string $domain
     * @param string $id
     * @param string $message
     * @return bool
     * @throws StringsException
     * @throws InvalidArgumentException
     */
    public function setMessage(string $localeCode, string $domain, string $id, string $message): bool
    {
        $messageCatalogue = $this->getCustomMessageCatalogue($localeCode);
        $metadata = [];
        if ($messageCatalogue->has($id, $domain)) {
            $messageCatalogue->set($id, $message, $domain);
            $metadata = ['notes' => [
                ['category' => 'state', 'content' => 'updated'],
                ['category' => 'iso8601', 'content' => (new DateTime())->format(DATE_ISO8601)]
            ]];
        } else {
            $messageCatalogue->add([$id => $message], $domain);
            $metadata = ['notes' => [
                ['category' => 'state', 'content' => 'new'],
                ['category' => 'iso8601', 'content' => (new DateTime())->format(DATE_ISO8601)]
            ]];
        }
        $messageCatalogue->setMetadata($id, $metadata, $domain);
        $this->save($messageCatalogue);

        $fullMessageCatalogue = $this->getFullMessageCatalogue();
        $revalidateCache = !$fullMessageCatalogue->has($id, $domain);
        if ($revalidateCache) {
            $this->getFullMessageCatalogue(true);
        }
        return $revalidateCache;
    }

    public function save(MessageCatalogue $messageCatalogue, string $format = 'xliff'): bool
    {
        try {
            $customMessagesPath = realpath($this->translatorDefaultPath);
            $dumper = new XliffFileDumper();
            $writer = new TranslationWriter();
            $writer->addDumper($format, $dumper);
            $writer->write($messageCatalogue, $format, ['path' => $customMessagesPath]);
            $this->clearCache();
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $localeCode
     * @return Locale|null
     * @throws Exception
     */
    public function addLocale(string $localeCode): ?Locale
    {
        /** @var Locale $locale */
        $locale = $this->localeRepository->findOneBy(['code' => $localeCode]);
        if ($locale instanceof Locale) {
            throw new Exception(sprintf('Locale "%s" already exists', $locale->getName()));
        }
        $locales = Locales::getLocales();
        if (!in_array($localeCode, $locales)) {
            throw new Exception(sprintf('Locale code "%s" not found', $localeCode));
        }
        $locale = new Locale();
        $locale->setCode($localeCode);
        $this->localeRepository->add($locale);
        return $locale;
    }

    /**
     * @param string $localeCode
     * @return bool|null
     * @throws Exception
     */
    public function removeLocale(string $localeCode): ?bool
    {
        /** @var Locale $locale */
        $locale = $this->localeRepository->findOneBy(['code' => $localeCode]);
        try {
            $this->localeRepository->remove($locale);
            return true;
        } catch (Exception $exception) {
            throw new Exception(sprintf('Failed to remove locale code "%s"', $localeCode));
        }
    }


    /**
     * @param $customMessagesPath
     * @param string $localeCode
     * @param MessageCatalogue $catalogue
     */
    private function feedCatalogue($customMessagesPath, string $localeCode, MessageCatalogue $catalogue): void
    {
        if ($this->filesystem->exists($customMessagesPath)) {
            $translationFiles = $this->finder->files()->name(sprintf('*.%s.*', $localeCode))->in($customMessagesPath);
            /** @var SplFileInfo $translationFile */
            foreach ($translationFiles as $translationFile) {
                list($domain, $translationLocaleCode, $format) = explode('.', $translationFile->getFilename());
                if (strtolower($localeCode) !== strtolower($translationLocaleCode)) {
                    continue;
                }
                $loader = null;
                switch (strtolower($format)) {
                    case 'yml':
                    case 'yaml':
                        $loader = new YamlFileLoader();
                        break;
                    case 'xlf':
                    case 'xliff':
                        $loader = new XliffFileLoader();
                }
                if (null === $loader) {
                    continue;
                }
                $domainCatalogue = $loader->load($translationFile->getRealPath(), $localeCode, $domain);
                foreach ($domainCatalogue->all($domain) as $id => $translation) {
                    if (!$catalogue->has($id, $domain) || $loader instanceof XliffFileLoader) {
                        $catalogue->set($id, $translation, $domain);
                    }
                }
            }
        }
    }

    private function clearCache()
    {
        $this->cacheClearer->clear($this->kernelTranslationsCacheDir);
        $this->filesystem->remove($this->kernelTranslationsCacheDir);
        $this->filesystem->mkdir($this->kernelTranslationsCacheDir);
        $this->warmer->warmUp($this->kernelTranslationsCacheDir);
    }
}
