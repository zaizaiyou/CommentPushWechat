<?php
/**
 * 微信推送评论通知
 * 
 * @package CommentPushWechat
 * @author 崽崽
 * @version 1.0
 * @link https://xll.cc
 */
class CommentPushWechat_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
    
        Typecho_Plugin::factory('Widget_Feedback')->comment = array('CommentPushWechat_Plugin', 'sc_send');
        Typecho_Plugin::factory('Widget_Feedback')->trackback = array('CommentPushWechat_Plugin', 'sc_send');
        Typecho_Plugin::factory('Widget_XmlRpc')->pingback = array('CommentPushWechat_Plugin', 'sc_send');
        
        return _t('请配置此插件的 openid, 以使您的微信推送生效');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $key = new Typecho_Widget_Helper_Form_Element_Text('openid', NULL, NULL, _t('openid'), _t('想要获取 openid 则需要在 <a href="https://push.xll.cc/">微信消息推送</a> 获取一个openid'));
        $form->addInput($key->addRule('required', _t('您必须填写一个正确的 openid')));
        
        $notMyself = new Typecho_Widget_Helper_Form_Element_Radio('notMyself',
            array(
                '1' => '是',
                '0' => '否'
            ),'1', _t('当评论者为自己时不发送通知'), _t('启用后，若评论者为博主，则不会发送微信通知<br><br><br>
            此插件由原作者 <a href="https://moe.best/">神代綺凜</a> 的 <a href="https://github.com/Tsuk1ko/Comment2Wechat">Comment2Wechat</a> 插件修改而来<br>
            
            本插件项目地址：<a href="https://github.com/zaizaiyou/CommentPushWechat">https://github.com/zaizaiyou/CommentPushWechat</a>
            '));
        $form->addInput($notMyself);
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 微信推送
     * 
     * @access public
     * @param array $comment 评论结构
     * @param Typecho_Widget $post 被评论的文章
     * @return void
     */
    public static function sc_send($comment, $post)
    {
        $options = Typecho_Widget::widget('Widget_Options')->plugin('CommentPushWechat');

        $openid = $options->openid;
        $notMyself = $options->notMyself;
        
        if($comment['authorId'] == 1 && $notMyself == '1'){
            return  $comment;
        }

        $title = "博客留言";
        $project = $comment['author']."在你的博客中说到";
        $content=$comment['text'];
        $ftqq = "https://push.xll.cc/push.php?openid=";

        $durl=$ftqq.$openid.'&title='.$title.'&project='.$project.'&content='.$content;
        
        function curl_file_get_contents($durl){ 

            $ch = curl_init(); 
        
            curl_setopt($ch, CURLOPT_URL, $durl); 
        
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回   
        
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回   
        
            $data = curl_exec($ch); 
        
            curl_close($ch); 
        
            return $data; 
        
        }

        $data=curl_file_get_contents($durl);
        
        
        return  $comment;
    }
}
