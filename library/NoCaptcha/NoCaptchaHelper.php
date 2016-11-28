<?php
namespace NoCaptcha;

use Zend\Captcha\AdapterInterface;
use Zend\Form\View\Helper\FormInput;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;


/**
 * Class ReCaptcha
 *
 * @link https://github.com/srggroup/ZF2NoCaptcha
 * @package NoCaptcha
 * @author  Adam Balint <adam.balint@srg.hu>
 */
class NoCaptchaHelper extends FormInput
{

	/**
	 * @param ElementInterface $element
	 *
	 * @return $this|string|FormInput
	 */
	public function __invoke(ElementInterface $element = null)
	{
		if (!$element) {
			return $this;
		}

		return $this->render($element);
	}


	/**
	 * @param ElementInterface $element
	 *
	 * @return string
	 */
	public function render(ElementInterface $element)
	{
		$captcha = $element->getCaptcha();

		if ($captcha === null || !$captcha instanceof AdapterInterface) {
			throw new Exception\DomainException(sprintf(
				'%s requires that the element has a "captcha" attribute implementing Zend\Captcha\AdapterInterface',
				__METHOD__
			));
		}

		$name = $element->getName();
		$id = $element->getAttribute('id') ? $element->getAttribute('id') : $name;

		$captchaPattern = '<div %s></div>';

		$captchaAttributes = $this->createAttributesString(array(
			'class' => 'g-recaptcha '.$element->getAttribute('class'),
			'data-sitekey' => $captcha->getSiteKey(),
			'data-theme' => $captcha->getTheme(),
			'data-type' => $captcha->getType(),
			'data-callback' => $captcha->getCallback(),
		));


		$captchaElement = sprintf($captchaPattern, $captchaAttributes);
		$input = $this->renderHiddenInput($id, $name);
		$js = $this->renderJsCallback($captcha->getCallback(), $id);

		return $captchaElement . $input . $js;
	}

	/**
	 * @param $id
	 * @param $name
	 *
	 * @return string
	 */
	protected function renderHiddenInput($id, $name)
	{
		$pattern = '<input type="hidden" %s%s';
		$closingBracket = $this->getInlineClosingBracket();

		$attributes = $this->createAttributesString(array(
			'id' =>  $id,
			'name' => $name,
            'class' => 'recaptchaResponse',
		));

		return sprintf($pattern, $attributes, $closingBracket);
	}

	/**
	 * @param $callback
	 * @param $id
	 *
	 * @return string
	 */
	protected function renderJsCallback($callback, $id)
	{
		$lang=LANG;
		$js='';
		$js.=<<<SCRIPT
<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl={$lang}"></script>
SCRIPT;

		$js .= <<<SCRIPT
<script type="text/javascript" language="JavaScript">
var {$callback} = function(response) {
        document.getElementById('{$id}').value = response;
      };

</script>
SCRIPT;

		return $js;

	}

}