<?php
/*
 * App_Application_Resource_Viewsmarty
 */
class App_Application_Resource_Viewsmarty extends Zend_Application_Resource_ResourceAbstract
{
    /**
     *
     * @return App_View_Smarty
     */
    public function init()
    {
        $options = $this->getOptions();

        //--------------------------------------------
        // API処理は除外する
        $uri = $_SERVER['REQUEST_URI'];
        if(preg_match('@rest/@i', $uri)) {
            return;
        }
        //--------------------------------------------

        // Pager
        Zend_Paginator::setDefaultScrollingStyle('Sliding');

        // View
        $view = new App_View_Smarty(null, $options);


        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setView($view);
        $templateFileName = '';

        // helper
        $viewRenderer->setViewBasePathSpec(realpath($options['template_dir']))
                     ->setViewScriptPathSpec(':controller/' . $templateFileName . ':action.:suffix')
                     ->setViewScriptPathNoControllerSpec($templateFileName . ':action.:suffix')
                     ->setViewSuffix($options['suffix'])
                    ;

        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        return $view;


    }

}
?>
