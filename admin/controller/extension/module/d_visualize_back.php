<?php
/*
*  location: admin/controller
*/

class ControllerExtensionModuleDVisualize extends Controller
{
    private $error = array();
    private $codename = 'd_visualize';
    private $route = 'extension/module/d_visualize';
    private $store_id = 0;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->language($this->route);
        $this->load->model($this->route);
        $this->load->model('extension/' . $this->codename . '/template');
        $this->load->model('extension/' . $this->codename . '/extension_helper');
        $this->load->model('extension/d_opencart_patch/url');
        $this->load->model('extension/d_opencart_patch/load');
        $this->load->model('extension/d_opencart_patch/user');
        $this->load->model('tool/image');
        $this->d_shopunity = (file_exists(DIR_SYSTEM . 'library/d_shopunity/extension/d_shopunity.json'));
        $this->d_admin_style = (file_exists(DIR_SYSTEM . 'library/d_shopunity/extension/d_admin_style.json'));
        $this->d_opencart_patch = (file_exists(DIR_SYSTEM . 'library/d_shopunity/extension/d_opencart_patch.json'));
        $this->extension = json_decode(file_get_contents(DIR_SYSTEM . 'library/d_shopunity/extension/' . $this->codename . '.json'), true);
        $this->d_twig_manager = (file_exists(DIR_SYSTEM . 'library/d_shopunity/extension/d_twig_manager.json'));
        $this->d_event_manager = (file_exists(DIR_SYSTEM . 'library/d_shopunity/extension/d_event_manager.json'));

        $this->store_id = (isset($this->request->post['store_id'])) ? $this->request->post['store_id'] : 0;
        $setting_visualize = $this->{'model_extension_module_' . $this->codename}->loadSetting();

        $this->setting_visualize = $setting_visualize['module_' . $this->codename . '_setting'];
        $this->status_visualize = isset($setting_visualize['module_' . $this->codename . '_status']) ? $setting_visualize['module_' . $this->codename . '_status'] : false;
        $this->model = 'model_extension_module_' . $this->codename;
        $this->model_template = 'model_extension_' . $this->codename . '_template';
        $this->model_helper = 'model_extension_' . $this->codename . '_extension_helper';
    }

    public function index()
    {
        $this->load->language($this->route);
        $this->document->setTitle($this->language->get('heading_title_main'));
        if ($this->d_shopunity) {
            $this->load->model('extension/d_shopunity/mbooth');
            $this->model_extension_d_shopunity_mbooth->validateDependencies($this->codename);
        }

        if ($this->d_event_manager) {
            $this->load->model('extension/module/d_event_manager');
            if (!$this->model_extension_module_d_event_manager->isCompatible()) {
                $this->model_extension_module_d_event_manager->installCompatibility();
            }
        }

        if ($this->d_twig_manager) {
            $this->load->model('extension/module/d_twig_manager');
            $this->model_extension_module_d_twig_manager->installCompatibility();
        }

        //init iframe_src
        if (!isset($this->session->data['iframe_url'])) {
            $param = array();
            if ($this->request->server['HTTPS']) {
                $store_url = HTTPS_SERVER;
                $catalog_url = HTTPS_CATALOG;
            } else {
                $store_url = HTTP_SERVER;
                $catalog_url = HTTP_CATALOG;
            }
            $this->session->data['iframe_url'] = $catalog_url;
        }

        $this->uninstallTheme();
        if ($this->status_visualize) {
            $this->installTheme();
        }
        if ($this->setting_visualize['engine'] == 'nuxt') {
            $nuxt_dist = 'view/javascript/d_visualize/nuxt_vurify/dist';
            $data['app'] = file_get_contents($nuxt_dist . '/index.html');
            $data['app'] = str_replace('/_nuxt', HTTPS_SERVER . $nuxt_dist . '/_nuxt', $data['app']);
            $files = glob($nuxt_dist . '/' . '_nuxt' . '/manifest*.js', GLOB_BRACE);
            //check manifest for 1.4.0 version
            foreach ($files as $file) {
                $file_content = file_get_contents($file);
                $pos = strpos($file_content, HTTPS_SERVER . $nuxt_dist);
                if ($pos === false) {
                    echo 'new';
                    file_put_contents($file, str_replace('/_nuxt/', HTTPS_SERVER . $nuxt_dist . '/_nuxt/', $file_content));
                }
            }
            $this->response->setOutput($this->load->view($this->route . '_nuxt', $data));
        } else {

            //Vue JS with Vuex and
            $this->document->addScript("view/javascript/d_vue/vue.min.js");
            $this->document->addScript("view/javascript/d_vuex/vuex.min.js");
            $this->document->addScript("view/javascript/d_vue_i18n/vue-i18n.min.js");
            $this->document->addScript("view/javascript/d_vue_router/vue-router.min.js");

            //Alertify
            $this->document->addScript('view/javascript/d_alertify/alertify.min.js');
            $this->document->addStyle('view/javascript/d_alertify/css/alertify.css');
            $this->document->addStyle('view/javascript/d_alertify/css/themes/bootstrap.css');

            //Other libraries
            $this->document->addScript('view/javascript/d_visualize/lib/sync.js');
            $this->document->addScript('view/javascript/d_visualize/lib/topgress.js');
            $this->document->addScript('view/javascript/d_visualize/lib/VueOptions.js');
            $this->document->addScript('view/javascript/d_visualize/lib/vue-model-vuex.js');
            $this->document->addScript('view/javascript/d_underscore/underscore-min.js');

            //visualize styles
            $this->document->addStyle('view/stylesheet/' . $this->codename . '/dist/' . $this->codename . '.css');

            //loader
            $this->document->addStyle('view/stylesheet/' . $this->codename . '/lib/load-awesome-1.1.0/css/timer.css');

            //font-awesome
            $this->document->addStyle('view/javascript/' . $this->codename . '/font/awesome/all.min.css');
            $this->document->addStyle('view/javascript/' . $this->codename . '/font/awesome/v4-shims.min.css');
//        $this->document->addStyle(HTTPS_CATALOG . 'catalog/view/theme/' . $this->codename . '/font/awesome/all.min.css');
//        $this->document->addStyle(HTTPS_CATALOG . 'catalog/view/theme/' . $this->codename . '/font/awesome/v4-shims.min.css');
//        $this->document->addStyle('https://use.fontawesome.com/releases/v5.2.0/css/all.css');
//        $this->document->addStyle('https://use.fontawesome.com/releases/v5.2.0/css/v4-shims.css');
            // Module data
            $data['vueTemplates'] = $this->{'model_extension_module_' . $this->codename}->getVueTemplates();
            $view_scripts = $this->{'model_extension_module_' . $this->codename}->getVueScripts();
            foreach ($view_scripts as $script) {
                $this->document->addScript($script);
            }

            $data['language_id'] = $this->config->get('config_language_id');
            $data['local'] = $this->prepareLocal();
            $data['options'] = $this->prepareOptions();

            $state = array();
            $state['config']['save_url'] = $this->model_extension_d_opencart_patch_url->ajax($this->route . '/save');
            $state['config']['save_template_url'] = $this->model_extension_d_opencart_patch_url->ajax($this->route . '/saveTemplateUrl');
            $state['config']['update_setting_url'] = $this->model_extension_d_opencart_patch_url->ajax($this->route . '/updateSetting');
            $state['config']['load_setting_url'] = $this->model_extension_d_opencart_patch_url->ajax($this->route . '/loadState');
            $state['config']['save_iframe_url'] = $this->model_extension_d_opencart_patch_url->ajax($this->route . '/saveIframeUrl');
            $data['state'] = $state;
            //out put view
            $data['setup'] = false;
            if (!$this->{'model_extension_module_' . $this->codename}->checkInstallModule()) {
                $data = $this->setup($data);
            }
            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');
            $this->response->setOutput($this->load->view($this->route, $data));
        }
    }

    public function opencartData()
    {
        $data['title'] = $this->language->get('heading_title_main');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['base_url'] = HTTPS_CATALOG;
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput(json_encode($data));
    }

    public function setup($data)
    {
        $data['setup'] = true;
        $data['text_setup_title'] = $this->language->get('text_setup_title');
        $data['text_setup_description'] = $this->language->get('text_setup_description');
        $data['setup_bottom_image'] = $this->model_tool_image->resize('catalog/' . $this->codename . '/Image_footer_Social_Login.svg', 100, 100);
        $data['setup_into_logo'] = $this->model_tool_image->resize('catalog/' . $this->codename . '/social_login_preview.svg', 100, 100);

        $data['features'][] = array(
            'text' => $this->language->get('text_setup_step_by_step'),
            'icon' => $this->model_tool_image->resize('catalog/' . $this->codename . '/step_by_step.svg', 100, 100)
        );
        $data['features'][] = array(
            'text' => $this->language->get('text_setup_soc_logins'),
            'icon' => $this->model_tool_image->resize('catalog/' . $this->codename . '/soc_logins.svg', 100, 100)
        );
        $data['features'][] = array(
            'text' => $this->language->get('text_setup_full_customize'),
            'icon' => $this->model_tool_image->resize('catalog/' . $this->codename . '/full_customize.svg', 100, 100)
        );
        $data['features'][] = array(
            'text' => $this->language->get('text_setup_gdpr_compilant'),
            'icon' => $this->model_tool_image->resize('catalog/' . $this->codename . '/gdpr_compilant.svg', 100, 100)
        );
        $data['text_button_setup'] = $this->language->get('button_setup');
        $data['button_setup'] = $this->model_extension_d_opencart_patch_url->ajax($this->route . '/setupUrl');

        return $data;
    }

    public function saveIframeUrl()
    {
        $this->session->data['iframe_url'] = html_entity_decode($this->request->post['last_url']);
        $json['success'] = $this->language->get('text_success');
        $this->response->setOutput(json_encode($json));
    }

    public function saveTemplateUrl()
    {

        $saved_template = $this->{$this->model_template}->saveTemplate(
            array(
                'template_codename' => $this->request->post['template_codename'],
                'template' => json_decode(html_entity_decode($this->request->post['template'], ENT_QUOTES, 'UTF-8'), true),
                'store_id' => $this->store_id)
        );
        $this->response->setOutput(json_encode(array('success' => $this->language->get('text_success_template'), 'template' => $saved_template)));

    }

    public function save()
    {
        try {
            $this->uninstallTheme();
            $new_post = array();
            foreach ($this->request->post as $k => $v) {
                $new_post['module_' . $this->codename . '_' . $k] = $v;
            }
            $this->model_extension_d_opencart_patch_setting->editSetting('module_' . $this->codename, $new_post, $this->store_id);
            $this->session->data['success'] = $this->language->get('text_success');
            $setting_visualize = $this->{'model_extension_module_' . $this->codename}->loadSetting();
            $this->setting_visualize = $setting_visualize['module_' . $this->codename . '_setting'];
            if ($this->request->post['status']) {
                $this->installTheme();
            }
            $this->response->setOutput(json_encode(array('success' => $this->session->data['success'])));
        } catch (Exception $e) {
            $this->session->data['error'] = $e;
            $this->response->setOutput(json_encode(array('error' => $this->session->data['error'])));
        }
    }


    public function loadState()
    {
        $json = array();
        $setting = array();
        $templates = array();
        foreach ($this->{'model_extension_' . $this->codename . '_template'}->getAvailableTemplates() as $template_k => $template) {
            $templates[$template_k] = $template;

        }
        $setting['active_template'] = $this->setting_visualize['active_template'];
        $setting['auto_save'] = $this->setting_visualize['auto_save'];
        $setting['status'] = (int)$this->status_visualize;
        $json['available_components'] = $this->{'model_extension_' . $this->codename . '_template'}->getAvailableComponents();
        $json['iframe_src'] = $this->session->data['iframe_url'];
        $json['templates'] = $templates;
        $json['setting'] = $setting;
        $json['success'] = $this->language->get('text_success');
//        $this->response->addHeader('Access-Control-Allow-Origin: *');
//        $this->response->addHeader('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
//        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function prepareLocal()
    {
        $local = array();

        $local['common']['heading_title'] = $this->language->get('heading_title_main');
        $local['common']['button_add'] = $this->language->get('button_add');
        $local['common']['button_save_and_stay'] = $this->language->get('button_save_and_stay');
        $local['common']['button_save'] = $this->language->get('button_save');
        $local['common']['button_cancel'] = $this->language->get('button_cancel');
        $local['common']['text_edit'] = $this->language->get('text_edit');
        $local['common']['text_yes'] = $this->language->get('text_yes');
        $local['common']['text_no'] = $this->language->get('text_no');
        $local['common']['text_enabled'] = $this->language->get('text_enabled');
        $local['common']['entry_status'] = $this->language->get('entry_status');
        $local['common']['entry_edit_theme'] = $this->language->get('entry_edit_theme');
        $local['common']['entry_deactivate'] = $this->language->get('entry_deactivate');
        $local['common']['entry_activate'] = $this->language->get('entry_activate');
        $local['common']['entry_collapse'] = $this->language->get('entry_collapse');

        $local['setting']['entry_auto_save_help'] = $this->language->get('entry_auto_save_help');

        $local['dashboard']['entry_available_templates'] = $this->language->get('entry_available_templates');

        $local['edit']['current_template'] = $this->language->get('entry_current_template');
        $local['edit']['active_template'] = $this->language->get('entry_active_template');
        $local['edit']['change_template'] = $this->language->get('entry_change');
        $local['edit']['change_template'] = $this->language->get('entry_change');
        $local['edit']['publish'] = $this->language->get('entry_publish');
        $local['edit']['vdh'] = $this->language->get('entry_visual_header');
        $local['edit']['vdf'] = $this->language->get('entry_visual_footer');
        $local['edit']['entry_common_components'] = $this->language->get('entry_common_components');
        $local['edit']['entry_component_skin'] = $this->language->get('entry_component_skin');
        $local['edit']['entry_custom_style'] = $this->language->get('entry_custom_style');
        $local['edit']['entry_d_product_thumb'] = $this->language->get('entry_d_product_thumb');
        $local['edit']['entry_breadcrumb'] = $this->language->get('entry_breadcrumb');
        $local['edit']['entry_d_product_sort'] = $this->language->get('entry_d_product_sort');
        $local['edit']['entry_*'] = $this->language->get('entry_*');

        $local['error']['load_content'] = $this->language->get('error_load_content');
        $local['error']['save_content'] = $this->language->get('error_save_content');
        $local['blocks'] = array();
        return array($this->config->get('config_language_id') => $local);
    }

    public function prepareOptions()
    {
        $option = array();
        // Breadcrumbs
        $option['common']['breadcrumbs'] = array();
        $option['common']['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->model_extension_d_opencart_patch_url->ajax('common/home')
        );

        $option['common']['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->model_extension_d_opencart_patch_url->getExtensionAjax('module')
        );

        $option['common']['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->model_extension_d_opencart_patch_url->ajax($this->route)
        );
        // Variable
        $option['common']['id'] = $this->codename;
        $option['common']['route'] = $this->route;
        $option['common']['store_id'] = $this->store_id;
        $option['common']['version'] = $this->extension['version'];
        $option['common']['token'] = $this->model_extension_d_opencart_patch_user->getUrlToken();
        $option['common']['d_shopunity'] = $this->d_shopunity;
        $option['common']['entry_get_update'] = sprintf($this->language->get('entry_get_update'), $this->extension['version']);
        // Multistore
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        // Url
        $url = '';
        if (isset($this->request->get['store_id'])) {
            $url .= '&store_id=' . $store_id;
        }

        $option['action']['cancel'] = $this->model_extension_d_opencart_patch_url->getExtensionAjax('module');
        $option['action']['module_link'] = $this->model_extension_d_opencart_patch_url->link($this->route);
        $option['action']['vdh'] = $this->model_extension_d_opencart_patch_url->ajax('extension/d_visual_designer/designer/frontend', 'config=d_visual_designer_header&id=0');
        $option['action']['vdf'] = $this->model_extension_d_opencart_patch_url->ajax('extension/d_visual_designer/designer/frontend', 'config=d_visual_designer_footer&id=0');
        $option['action']['action'] = $this->model_extension_d_opencart_patch_url->ajax($this->route, $url);
        $option['action']['cancel'] = $this->model_extension_d_opencart_patch_url->getExtensionAjax('module');
        $option['img']['no_image'] = $this->model_tool_image->resize("no_image.png", 300, 400);
        $option['img']['no_data_img'] = 'view/image/' . $this->codename . '/nodata.png';
        $option['img']['desktop_frame'] = 'view/image/' . $this->codename . '/desktop_frame.png';
        $option['img']['mobile_frame'] = 'view/image/' . $this->codename . '/mobile_frame.png';

        return $option;
    }

    public function setupUrl()
    {
        $this->load->model('extension/d_opencart_patch/url');
        $this->{$this->model}->installConfig();
        $this->response->redirect($this->model_extension_d_opencart_patch_url->ajax($this->route));
    }

    public function uninstallTheme()
    {
        $setting = $this->model_extension_d_opencart_patch_setting->getSetting('theme_default');
        $setting['theme_default_directory'] = 'default';
        $this->model_extension_d_opencart_patch_setting->editSetting('theme_default', $setting);
        $this->{$this->model}->uninstallEvents();
    }

    public function installTheme()
    {
        $setting = $this->model_extension_d_opencart_patch_setting->getSetting('theme_default');
        $setting['theme_default_directory'] = $this->codename; // 32 work
        $this->model_extension_d_opencart_patch_setting->editSetting('theme_default', $setting);
        $this->{$this->model}->uninstallEvents($this->setting_visualize['active_template']);
        $this->{$this->model}->installEvents($this->setting_visualize['active_template']);
        $this->{$this->model_helper}->installVD($this->setting_visualize['active_template']);
        $this->{$this->model_helper}->installDependencyModules($this->setting_visualize['active_template']);
        $this->{$this->model_helper}->installConfigThemeDefaults();
        $this->{$this->model_helper}->installTemplateThemeDefaults($this->setting_visualize['active_template']);
    }

    public function install()
    {
        if ($this->d_shopunity) {
            $this->load->model('extension/d_shopunity/mbooth');
            $this->model_extension_d_shopunity_mbooth->installDependencies($this->codename);
        }
        $this->{$this->model}->installDataBase();
    }

    public function uninstall()
    {
        $this->uninstallTheme();
    }
}
