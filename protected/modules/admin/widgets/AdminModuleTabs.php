<?php

namespace admin\widgets;

class AdminModuleTabs extends \CWidget
{
    public $tabsMap = [];
    public $actionId;

    public $setPageTitle = true;

    public function run()
    {
        $links = [];

        foreach ($this->tabsMap as $path => $name) {
            if ($path == '') {
                $path = 'index';
            }

            if (is_array($name)) {
                $hide = 0;

                switch ($name['display']) {
                    // Определяем показывать ли этот таб
                    case 'whenActive':
                        if ($this->actionId != $path) {
                            $hide = 1;
                        }
                        break;
                    case 'index':
                        if (!($this->actionId == 'index' || $this->actionId == 'create' || $this->actionId == 'update')) {
                            $hide = 1;
                        }
                        break;
                    default:
                        if (strpos($this->actionId, $name['display']) === false) {
                            $hide = 1;
                        }
                        break;
                }

                if ($hide) {
                    continue;
                }

                $name = $name['name'];
            }

            $isActive = $this->actionId === str_replace('/', '', $path);

            if ($this->setPageTitle && $isActive) {
                $this->controller->pageTitle = $name;
            }

            $links[] = [
                'text' => $name,
                'url' => $this->controller->createUrl($this->controller->id . '/' . $path),
                'htmlOptions' => [
                    'class' => $isActive ? 'active' : '',
                ],
            ];
        }

        $this->render('admin_module_tabs', [
            'links' => $links,
        ]);
    }
}
