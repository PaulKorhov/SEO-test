<?php
class PDLoader {
    private array $profiles = [];
    private string $csv_path;

    public function __construct($csv_path) {
        $this->csv_path = $csv_path;
        $this->load_profiles();
    }

    /**
     * @throws Exception
     */
    private function load_profiles(): void
    {
        if (!file_exists($this->csv_path)) {
            throw new Exception("CSV file not found: " . $this->csv_path);
        }

        $handle = fopen($this->csv_path, 'r');
        if ($handle === false) return;

        $headers = fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== false) {
            $profile = array_combine($headers, $row);
            $this->profiles[$profile['slug']] = $profile;
        }

        fclose($handle);
    }

    public function get_profile($slug) {
        return $this->profiles[$slug] ?? null;
    }

    public function get_all_profiles(): array
    {
        return $this->profiles;
    }

    public function profile_exists($slug): bool
    {
        return isset($this->profiles[$slug]);
    }
}