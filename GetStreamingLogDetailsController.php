<?php
class GetStreamingLogDetailsController extends Controller {
    protected function beforeAction($action) {
        parent::beforeAction($action);
        if (!(Yii::app()->user->id)) {
            $studio_id = Yii::app()->common->getStudiosId();
            $std_config = StudioConfig::model()->getConfig($studio_id, 'free_content_login');
            $free_content_login = 1;
            if (isset($std_config->config_value) && $std_config->config_value == 0) {
                $free_content_login = 0;
            }
            if (intval($free_content_login)) {
                Yii::app()->user->setFlash('error', 'Login to access the page.');
                $this->redirect(Yii::app()->getBaseUrl(TRUE) . '/user/login');
                exit;
            }
        }
        return true;
    }
    /*
     *@author :   Aravind 
     *@email  :   aravindgithub@gmail.com
     *@reason :   Audio data for playing :  
     *@functionality : actionGetStreamingLogDetails
     *@date   :   08-08-2018
     *@purpose : get stream content log information
     */
    public function actionGetStreamingLogDetails(){
        $data_information = Yii::app()->GSLD->actiongetStreamingLogDetails(1);
    }
}

