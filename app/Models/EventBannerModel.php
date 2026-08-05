<?php

namespace App\Models;

use CodeIgniter\Model;

class EventBannerModel extends Model
{
    protected $table = 'event_banners';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'title',
        'image_url',
        'link_url',
        'is_active',
        'start_date',
        'end_date',
        'allowed_domains',
        'domain_specific',
        'created_at',
        'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get active event banners for current date and domain
     */
    public function getActiveEventBanners($currentDomain = null)
    {
        $currentDate = date('Y-m-d H:i:s');
        
        $builder = $this->where('is_active', 1)
                        ->where('start_date <=', $currentDate)
                        ->where('end_date >=', $currentDate);
        
        // If no domain provided, return all active events
        if (!$currentDomain) {
            return $builder->orderBy('created_at', 'DESC')->findAll();
        }
        
        // Get all active events first
        $allEvents = $builder->orderBy('created_at', 'DESC')->findAll();
        
        // Filter by domain using PHP (more reliable than JSON_CONTAINS)
        $filteredEvents = [];
        
        foreach ($allEvents as $event) {
            // Include non-domain-specific events
            if (empty($event['domain_specific']) || $event['domain_specific'] == 0) {
                $filteredEvents[] = $event;
                continue;
            }
            
            // Check domain-specific events
            if ($event['domain_specific'] == 1 && !empty($event['allowed_domains'])) {
                $allowedDomains = json_decode($event['allowed_domains'], true);
                
                if (is_array($allowedDomains)) {
                    foreach ($allowedDomains as $allowedDomain) {
                        // Check if current domain contains the allowed domain
                        // or if they match exactly
                        if (strpos($currentDomain, $allowedDomain) !== false || 
                            strpos($allowedDomain, $currentDomain) !== false ||
                            $currentDomain === $allowedDomain) {
                            $filteredEvents[] = $event;
                            break; // Don't add the same event multiple times
                        }
                    }
                }
            }
        }
        
        return $filteredEvents;
    }

    /**
     * Get active event banner for current date and domain (single result)
     */
    public function getActiveEventBanner($currentDomain = null)
    {
        $banners = $this->getActiveEventBanners($currentDomain);
        return !empty($banners) ? $banners[0] : null;
    }

    /**
     * Get all event banners for admin
     */
    public function getAllEventBanners()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }
    
    /**
     * Debug method to check domain filtering
     */
    public function debugDomainFiltering($currentDomain = null)
    {
        $currentDate = date('Y-m-d H:i:s');
        $allEvents = $this->where('is_active', 1)
                          ->where('start_date <=', $currentDate)
                          ->where('end_date >=', $currentDate)
                          ->findAll();
        
        $debug = [
            'current_domain' => $currentDomain,
            'current_date' => $currentDate,
            'total_active_events' => count($allEvents),
            'events' => []
        ];
        
        foreach ($allEvents as $event) {
            $eventDebug = [
                'id' => $event['id'],
                'title' => $event['title'],
                'domain_specific' => $event['domain_specific'] ?? 0,
                'allowed_domains_raw' => $event['allowed_domains'] ?? null,
                'allowed_domains_parsed' => null,
                'is_included' => false,
                'reason' => ''
            ];
            
            // Parse allowed domains
            if (!empty($event['allowed_domains'])) {
                $eventDebug['allowed_domains_parsed'] = json_decode($event['allowed_domains'], true);
            }
            
            // Check inclusion logic
            if (empty($event['domain_specific']) || $event['domain_specific'] == 0) {
                $eventDebug['is_included'] = true;
                $eventDebug['reason'] = 'Non-domain-specific event';
            } elseif ($event['domain_specific'] == 1 && !empty($event['allowed_domains'])) {
                $allowedDomains = json_decode($event['allowed_domains'], true);
                if (is_array($allowedDomains)) {
                    foreach ($allowedDomains as $allowedDomain) {
                        if (strpos($currentDomain, $allowedDomain) !== false || 
                            strpos($allowedDomain, $currentDomain) !== false ||
                            $currentDomain === $allowedDomain) {
                            $eventDebug['is_included'] = true;
                            $eventDebug['reason'] = "Matched domain: {$allowedDomain}";
                            break;
                        }
                    }
                    if (!$eventDebug['is_included']) {
                        $eventDebug['reason'] = "No domain match found";
                    }
                } else {
                    $eventDebug['reason'] = "Invalid allowed_domains JSON";
                }
            } else {
                $eventDebug['reason'] = "Domain-specific but no allowed_domains";
            }
            
            $debug['events'][] = $eventDebug;
        }
        
        return $debug;
    }
}
