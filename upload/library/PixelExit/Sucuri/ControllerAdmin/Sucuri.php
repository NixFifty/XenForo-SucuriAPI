<?php

/**
 * @author      Aakif N. <webmaster@nixfifty.com>
 * @copyright   ? 2015 pixelexit.com. All rights reserved.
 * @link        https://pixelexit.com
 * @package		PixelExit_Sucuri
 */

class PixelExit_Sucuri_ControllerAdmin_Sucuri extends XenForo_ControllerAdmin_Abstract
{
	public function actionIndex()
	{
		$output = $this->getSucuriApi('show_settings');

		switch($output['output']['cache_mode'])
		{
			case 'docache':
				$output['cachePhrase'] = 'pe_sucuri_docache';
				break;
			case 'nocache':
				$output['cachePhrase'] = 'pe_sucuri_nocache';
				break;
			case 'sitecache':
				$output['cachePhrase'] = 'pe_sucuri_sitecache';
				break;
			case 'nocacheatall':
			default:
				$output['cachePhrase'] = 'pe_sucuri_nocacheatall';
				break;
		}

		switch($output['output']['security_level'])
		{
			case 'high':
				$output['securityPhrase'] = 'pe_sucuri_high';
				break;
			case 'paranoid':
				$output['securityPhrase'] = 'pe_sucuri_paranoid';
				break;
			default:
				$output['securityPhrase'] = 'pe_sucuri_unknown';
		}

		$output['cachePhrase'] = new XenForo_Phrase($output['cachePhrase']);
		$output['securityPhrase'] = new XenForo_Phrase($output['securityPhrase']);

		$viewParams = array(
			'sucuri' => $output,
			'success' => $this->_input->filterSingle('success', XenForo_Input::UINT)
		);

		return $this->responseView('PixelExit_Sucuri_ViewAdmin_Index', 'pe_sucuri_index', $viewParams);
	}

	public function actionClearCache()
	{
		$this->_assertPostOnly();

		$success = false;

		if ($this->_input->filterSingle('confirm', XenForo_Input::UINT))
		{
			$this->getSucuriApi('clear_cache');
			$success = true;
		}

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('sucuri', null, array('success' => $success))
		);
	}

	protected function getSucuriApi($action)
	{
		$xenOptions = XenForo_Application::getOptions();

		$ch = curl_init();
		$url = "https://waf.sucuri.net/api?v2";
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, true);

		$data = array(
			'k' => $xenOptions->peSucuriApiKey,
			's' => $xenOptions->peSucuriApiSecret,
			'a' => $action
		);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($ch);

		curl_close($ch);

		$output = json_decode($output);
		$output = json_decode(json_encode($output), true);

		if (!($output['output']) AND !($action == 'clear_cache'))
		{
			/** @var XenForo_Model_Option $optionModel */
			$optionModel = $this->getModelFromCache('XenForo_Model_Option');
			$group = $optionModel->getOptionGroupById('peSucuri');

			$url = XenForo_Link::buildAdminLink('options/list', $group);

			throw $this->responseException(
				$this->responseError(new XenForo_Phrase(
					'to_use_sucuri_integration_must_enter_application_info',
					array('url' => $url)
				))
			);
		}

		return $output;
	}
}