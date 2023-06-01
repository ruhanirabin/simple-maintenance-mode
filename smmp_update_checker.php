class SMMP_Update_Checker {
    private $repo_url;
    private $plugin_file;
    private $remote_info = null;

    public function __construct($repo_url, $plugin_file) {
        $this->repo_url = $repo_url;
        $this->plugin_file = $plugin_file;

        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
        add_filter('plugins_api', array($this, 'plugins_api_handler'), 10, 3);
    }

    private function get_remote_info() {
        if ($this->remote_info !== null) {
            return $this->remote_info;
        }

        $response = wp_remote_get($this->repo_url . '/releases/latest');

        if (is_wp_error($response)) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response));
        $download_url = $body->assets[0]->browser_download_url;
        $version = $body->tag_name;

        $this->remote_info = array(
            'version' => $version,
            'download_url' => $download_url
        );

        return $this->remote_info;
    }

    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote_info = $this->get_remote_info();

        if (!$remote_info) {
            return $transient;
        }

        $plugin_data = get_plugin_data($this->plugin_file);
        $plugin_slug = plugin_basename($this->plugin_file);

        if (version_compare($plugin_data['Version'], $remote_info['version'], '<')) {
            $transient->response[$plugin_slug] = (object) array(
                'new_version' => $remote_info['version'],
                'package' => $remote_info['download_url'],
                'slug' => $plugin_slug
            );
        }

        return $transient;
    }

    public function plugins_api_handler($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if ($args->slug !== sanitize_title(plugin_basename($this->plugin_file))) {
            return $result;
        }

        $remote_info = $this->get_remote_info();

        if (!$remote_info) {
            return $result;
        }

        $plugin_data = get_plugin_data($this->plugin_file);

        return (object) array(
            'name' => $plugin_data['Name'],
            'version' => $remote_info['version'],
            'download_link' => $remote_info['download_url']
        );
    }
}
