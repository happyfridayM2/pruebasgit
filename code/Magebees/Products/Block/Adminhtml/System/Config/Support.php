<?php
namespace Magebees\Products\Block\Adminhtml\System\Config;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
class Support extends \Magento\Config\Block\System\Config\Form\Field
{	
	public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
       $html = '';
		$html .= '<div style="float: left;">
<a href="https://www.magebees.com" target="_blank"><img src="https://www.magebees.com/skin/frontend/default/magentoextensiondesign/images/logo.gif" style="float:left; padding-right: 35px; margin-top: 30px;" /></a></div>
<div style="float:left">
<h2><b>MageBees Import Export Products Extension</b></h2>
<p>
<b>Installed Version: v1.4.2</b><br>
Website: <a target="_blank" href="https://www.magebees.com">https://www.magebees.com</a><br>
Like, share and follow us on 
<a target="_blank" href="https://www.facebook.com/magebees">Facebook</a>, 
<a target="_blank" href="https://plus.google.com/103198825494380131025">Google+</a> and
<a target="_blank" href="https://twitter.com/magebees">Twitter</a>.<br>
Do you need Extension Support? Please create support ticket from <a href="http://support.magebees.com" target="_blank">here</a> or <br> please contact us on <a href="mailto:support@magebees.com">support@magebees.com</a> for quick reply.
</p>
</div>';	
        return $html;
    }
}