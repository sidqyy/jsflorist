<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['setting_key', 'setting_value'];

    // Timestamps
    protected $useTimestamps = false;

    /**
     * Get a setting value by its key.
     *
     * @param string $key The setting key (e.g., 'visitor_count').
     * @return string|null The value of the setting or null if not found.
     */
    public function getSetting(string $key)
    {
        $setting = $this->where('setting_key', $key)->first();
        return $setting ? $setting['setting_value'] : null;
    }

    /**
     * Increment a numeric setting value.
     * Creates the setting if it doesn't exist.
     *
     * @param string $key   The setting key to increment.
     * @param int    $value The value to increment by.
     * @return bool
     */
    public function increment(string $key, int $value = 1)
    {
        $this->db->transStart();

        // Lock the row for update to prevent race conditions
        $sql = "SELECT * FROM {$this->table} WHERE setting_key = ? FOR UPDATE";
        $query = $this->db->query($sql, [$key]);
        $setting = $query->getRowArray();



        if ($setting) {
            $newValue = (int)$setting['setting_value'] + $value;
            $this->where('setting_key', $key)->set('setting_value', $newValue)->update();
        } else {
            // If the setting doesn't exist, create it with the initial increment value.
            $this->insert([
                'setting_key'   => $key,
                'setting_value' => $value
            ]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
