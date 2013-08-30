<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Kay Strobach <typo3@kay-strobach.de>
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 *
 *
 * @package voice
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Voice_Controller_IssueController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * issueRepository
	 *
	 * @var Tx_Voice_Domain_Repository_IssueRepository
	 */
	protected $issueRepository;

	/**
	 * indexAction
	 *
	 * @return
	 */
	public function indexAction() {
		$issue = new Tx_Voice_Domain_Model_Issue();
		$this->view->assign('issue', $issue);
	}


	/**
	 * action create
	 *
	 * @param Tx_Voice_Domain_Model_Issue $issue
	 * @return void
	 */
	public function createAction(Tx_Voice_Domain_Model_Issue $issue) {

		$extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$templateRootPath = t3lib_div::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath']);
		$templatePathAndFilename = $templateRootPath . 'Email/Index.html';

		/**
		 * @var $emailView Tx_Fluid_View_StandaloneView
		 */
		$emailHtmlView = $this->objectManager->create('Tx_Fluid_View_StandaloneView');
		$emailHtmlView->assignMultiple(
			array(
				'issue' => $issue
			)
		);
		$emailTextView = clone $emailHtmlView;

		$emailHtmlView->setFormat('html');
		$emailHtmlView->setTemplatePathAndFilename($templatePathAndFilename);

		// html rendering
		$htmlEmailBody = $emailHtmlView->render();
		unset($emailHtmlView);

		// render plain text as well
		$emailTextView->setFormat('txt');
		$emailTextView->setTemplatePathAndFilename($templateRootPath . 'Email/Index.txt');
		$textEmailBody = $emailTextView->render();
		/**
		 * @var $mail t3lib_mail_message
		 */
		$mail = t3lib_div::makeInstance('t3lib_mail_message');
		$mail->setFrom(array($issue->getEmail() => $issue->getName()))
			->setTo(array('info@kay-strobach.de' => 'Kay'))
			->setSubject($issue->getSubject())
			->setBody($textEmailBody, 'text/plain')
			->addPart($htmlEmailBody, 'text/html')
			->attach(new Swift_Attachment(print_r(json_decode($issue->getCollectedData()), TRUE), 'trace.txt', 'text/plain'))
			->attach(new Swift_Attachment($issue->getScreenshotAsFile(), 'screen.png', 'image/png'))
			->send();
		$this->issueRepository->add($issue);

		$this->view->assign('issue', $issue);
	}

}
?>