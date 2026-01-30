<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain\Enums;

/**
 * Bot/Crawler type enumeration.
 */
enum BotType: string
{
    case Googlebot = 'googlebot';
    case Bingbot = 'bingbot';
    case YandexBot = 'yandexbot';
    case Baiduspider = 'baiduspider';
    case DuckDuckBot = 'duckduckbot';
    case Slurp = 'slurp'; // Yahoo
    case FacebookBot = 'facebookbot';
    case TwitterBot = 'twitterbot';
    case LinkedInBot = 'linkedinbot';
    case AppleBot = 'applebot';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Googlebot => 'Googlebot',
            self::Bingbot => 'Bingbot',
            self::YandexBot => 'YandexBot',
            self::Baiduspider => 'Baiduspider',
            self::DuckDuckBot => 'DuckDuckBot',
            self::Slurp => 'Yahoo! Slurp',
            self::FacebookBot => 'Facebook Bot',
            self::TwitterBot => 'Twitter Bot',
            self::LinkedInBot => 'LinkedIn Bot',
            self::AppleBot => 'Applebot',
        };
    }

    /**
     * Get the User-Agent string for this bot.
     */
    public function getUserAgent(): string
    {
        return match ($this) {
            self::Googlebot => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            self::Bingbot => 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
            self::YandexBot => 'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)',
            self::Baiduspider => 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)',
            self::DuckDuckBot => 'DuckDuckBot/1.0; (+http://duckduckgo.com/duckduckbot.html)',
            self::Slurp => 'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)',
            self::FacebookBot => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
            self::TwitterBot => 'Twitterbot/1.0',
            self::LinkedInBot => 'LinkedInBot/1.0 (compatible; Mozilla/5.0; Apache-HttpClient +http://www.linkedin.com)',
            self::AppleBot => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15 (Applebot/0.1)',
        };
    }

    /**
     * Get mobile variant of the bot UA if available.
     */
    public function getMobileUserAgent(): string
    {
        return match ($this) {
            self::Googlebot => 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            self::Bingbot => 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Mobile Safari/537.36 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
            default => $this->getUserAgent(), // Fall back to desktop version
        };
    }
}
