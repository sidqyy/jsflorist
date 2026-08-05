<?php

if (!function_exists('getCurrentDomain')) {
    /**
     * Get current domain name
     */
    function getCurrentDomain(): string
    {
        return $_SERVER['HTTP_HOST'] ?? 'localhost';
    }
}

if (!function_exists('getDomainName')) {
    /**
     * Get simplified domain name for display
     */
    function getDomainName(string $domain): string
    {
        $names = [
            'jsflorist.com' => 'JS Florist',
            'localhost' => 'Local Development'
        ];
        
        // Check for partial matches
        foreach ($names as $domainKey => $name) {
            if (strpos($domain, $domainKey) !== false) {
                return $name;
            }
        }
        
        return $domain;
    }
}

if (!function_exists('isEventAllowedForDomain')) {
    /**
     * Check if event banner is allowed for current domain
     */
    function isEventAllowedForDomain(array $eventBanner, string $currentDomain): bool
    {
        // If not domain specific, allow for all domains
        if (($eventBanner['domain_specific'] ?? 0) == 0) {
            return true;
        }
        
        // If domain specific, check if current domain is in allowed list
        $allowedDomains = json_decode($eventBanner['allowed_domains'] ?? '[]', true);
        
        if (empty($allowedDomains)) {
            return false;
        }
        
        // Check for exact match or partial match
        foreach ($allowedDomains as $allowedDomain) {
            if (strpos($currentDomain, $allowedDomain) !== false) {
                return true;
            }
        }
        
        return false;
    }
}
