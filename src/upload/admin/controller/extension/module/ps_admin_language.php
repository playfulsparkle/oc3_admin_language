<?php
class ControllerExtensionModulePsAdminLanguage extends Controller
{
    /**
     * @var string The support email address.
     */
    const EXTENSION_EMAIL = 'support@playfulsparkle.com';

    /**
     * @var string The documentation URL for the extension.
     */
    const EXTENSION_DOC = 'https://github.com/playfulsparkle/oc3_admin_language.git';

    private $error = array();

    public function index()
    {
        $this->load->language('extension/module/ps_admin_language');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_ps_admin_language', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/ps_admin_language', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/ps_admin_language', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['module_ps_admin_language_status'])) {
            $data['module_ps_admin_language_status'] = (bool) $this->request->post['module_ps_admin_language_status'];
        } else {
            $data['module_ps_admin_language_status'] = (bool) $this->config->get('module_ps_admin_language_status');
        }

        $data['text_contact'] = sprintf($this->language->get('text_contact'), self::EXTENSION_EMAIL, self::EXTENSION_EMAIL, self::EXTENSION_DOC);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/ps_admin_language', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/ps_admin_language')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }


    public function install()
    {
        $this->load->model('setting/setting');

        $data = array(
            'module_ps_admin_language_status' => 0,
        );

        $this->model_setting_setting->editSetting('module_ps_admin_language', $data);
    }

    public function uninstall()
    {

    }

    public function save()
    {
        $this->load->language('common/language');

        $json = [];

        if (isset($this->request->post['code'])) {
            $code = (string) $this->request->post['code'];
        } else {
            $code = '';
        }

        if (isset($this->request->post['redirect'])) {
            $redirect = html_entity_decode((string) $this->request->post['redirect'], ENT_QUOTES, 'UTF-8');
        } else {
            $redirect = '';
        }

        // Language
        $this->load->model('localisation/language');

        $language_info = $this->model_localisation_language->getLanguageByCode($code);

        if (!$language_info) {
            $json['error'] = $this->language->get('error_language');
        }

        if (!$json) {
            setcookie('language', $code, time() + 60 * 60 * 24 * 30, '/');

            if ($this->request->server['HTTPS']) {
                $config_url = HTTPS_CATALOG;
            } else {
                $config_url = HTTP_CATALOG;
            }

            if ($redirect && strpos($redirect, $config_url) === 0) {
                $json['redirect'] = $redirect;
            } else {
                $json['redirect'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function list()
    {
        // Language
        $data['languages'] = [];

        $this->load->model('localisation/language');

        $results = $this->model_localisation_language->getLanguages();

        foreach ($results as $result) {
            $data['languages'][] = [
                'href' => 'index.php?route=extension/module/ps_admin_language/save&user_token=' . $this->session->data['user_token'],
                'name' => $result['name'],
                'code' => $result['code'],
                'image' => 'language/' . $result['code'] . '/' . $result['code'] . '.png'
            ];
        }

        if (isset($this->request->cookie['language']) && isset($results[$this->request->cookie['language']])) {
            $data['code'] = $this->request->cookie['language'];
        } else {
            $data['code'] = $this->config->get('config_admin_language');
        }

        // Redirect
        $url_data = $this->request->get;

        if (isset($url_data['route'])) {
            $route = $url_data['route'];
        } else {
            $route = 'common/dashboard';
        }

        unset($url_data['route']);

        $url = '';

        if ($url_data) {
            $url .= '&' . urldecode(http_build_query($url_data));
        }

        $data['redirect'] = $this->url->link($route, $url);

        return $this->load->view('extension/module/ps_admin_language_list', $data);
    }
}
