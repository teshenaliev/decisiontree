<?php 
class ExportController{

	private $DecisionTree;
	private $QuestionnaireController;
	private $objPHPExcel;
	private $currentUser;
	private $currentUserQuestionnaireTree;
	private $headerStyle;
	private $activeSheetIndex;

	public function __construct($DecisionTree)
	{
		$this->DecisionTree = $DecisionTree;
		$this->QuestionnaireController = $DecisionTree->QuestionnaireController;
		$this->currentUser = $this->DecisionTree->get_user_with_meta($_GET['user_id']);
		if (isset($this->currentUser->meta_data['questionnaire_tree'][0])){
			$this->currentUserQuestionnaireTree = unserialize($this->currentUser->meta_data['questionnaire_tree'][0]);
		}
		$this->activeSheetIndex = 0;
		$this->headerStyle = array(
			'font' => array(
				'bold' => true,
			),
			'borders' => array(
				'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
				),
			),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'argb' => 'FFA0A0A0',
				),
				'endcolor' => array(
					'argb' => 'FFA0A0A0',
				),
			),
		);

	}

	public function exportReport()
	{
		$this->objPHPExcel = new PHPExcel();

		$this->objPHPExcel->getProperties()->setCreator("Espiru")
									 ->setTitle("Zoom Report")
									 ->setSubject("Zoom Report")
									 ->setKeywords("office 2007 openxml");
		$this->TakeOffSheet();
		$this->InformationSheet();
		$this->QuestionResponseSheet();
		$this->MobilizationSheet();
		$this->objPHPExcel->setActiveSheetIndex(0);


		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$this->currentUser->data->meta_data['first_name'][0].'-'.$this->currentUser->data->meta_data['last_name'][0].'-report.xlsx"');
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');

		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
	    exit();
	}
	/*
		values with price
	*/
	private function TakeOffSheet()
	{
		$takeOffSheet = new PHPExcel_Worksheet($this->objPHPExcel, 'Take off');
		// Attach the “My Data” worksheet as the first worksheet in the PHPExcel object
		$this->objPHPExcel->addSheet($takeOffSheet, $this->activeSheetIndex);
		//echo print_r($this->currentUserQuestionnaireTree);
		$answeredQuestions = $this->QuestionnaireController->getAnsweredQuestions($this->currentUserQuestionnaireTree);
		$row = 1;
		$this->objPHPExcel->setActiveSheetIndex($this->activeSheetIndex)
	            ->setCellValue('A'. $row, 'Title')
	            ->setCellValue('B'. $row, 'Value')
	            ->setCellValue('C'. $row, 'Price')
	            ->setCellValue('D'. $row, 'Total');
		$this->objPHPExcel->setActiveSheetIndex($this->activeSheetIndex)->getStyle('A1:D1')->applyFromArray($this->headerStyle);
		foreach($answeredQuestions as $key => $singleQuestion){
			$row++;
			$this->objPHPExcel->setActiveSheetIndex($this->activeSheetIndex)
	            ->setCellValue('A'. $row, $singleQuestion['post_title'])
	            ->setCellValue('B'. $row, $singleQuestion['value'])
	            ->setCellValue('C'. $row, $singleQuestion['price'])
	    		->setCellValue('D'. $row, '='.'B'. $row .'*'.'C'. $row);
		}
		$this->objPHPExcel->getActiveSheet()->setTitle('Take off');
		$this->activeSheetIndex++;
	}
	/*
		just answers
	*/
	private function InformationSheet()
	{
		$InformationSheet = new PHPExcel_Worksheet($this->objPHPExcel, 'Information');
		// Attach the “My Data” worksheet as the first worksheet in the PHPExcel object
		$this->objPHPExcel->addSheet($InformationSheet, $this->activeSheetIndex);

		$answeredQuestions = $this->QuestionnaireController->getAnsweredQuestionsWithParent($this->currentUserQuestionnaireTree);
		$row = 1;
		$this->objPHPExcel->setActiveSheetIndex($this->activeSheetIndex)
		            ->setCellValue('A'. $row, 'Title')
		            ->setCellValue('B'. $row, 'Value')
		            ->setCellValue('C'. $row, 'Price')
		            ->setCellValue('D'. $row, 'Total');
		$this->objPHPExcel->setActiveSheetIndex($this->activeSheetIndex)->getStyle('A1:D1')->applyFromArray($this->headerStyle);
		foreach($answeredQuestions as $key => $singleQuestion){
			$row++;
			$this->objPHPExcel->setActiveSheetIndex($this->activeSheetIndex)
	            ->setCellValue('A'. $row, $singleQuestion['post_title'])
	            ->setCellValue('B'. $row, $singleQuestion['value'])
	            ->setCellValue('C'. $row, $singleQuestion['price'])
	 			->setCellValue('D'. $row, '='.'B'. $row .'*'.'C'. $row);
		}
		$this->objPHPExcel->getActiveSheet()->setTitle('Information');
		$this->activeSheetIndex++;
	}
	/*
		unanswered questions
	*/
	private function QuestionResponseSheet()
	{
		$takeOffSheet = new PHPExcel_Worksheet($this->objPHPExcel, 'Question Response');
		// Attach the “My Data” worksheet as the first worksheet in the PHPExcel object
		$this->objPHPExcel->addSheet($takeOffSheet, $this->activeSheetIndex);
		//echo print_r($this->currentUserQuestionnaireTree);
		$answeredQuestions = $this->QuestionnaireController->getUnAnsweredQuestions($this->currentUserQuestionnaireTree);
		$row = 1;
		$this->objPHPExcel->setActiveSheetIndex($this->activeSheetIndex)
	            ->setCellValue('A'. $row, 'ID')
	            ->setCellValue('B'. $row, 'Title')
	            ->setCellValue('C'. $row, 'Status');
		$this->objPHPExcel->setActiveSheetIndex($this->activeSheetIndex)->getStyle('A1:C1')->applyFromArray($this->headerStyle);
		foreach($answeredQuestions as $key => $singleQuestion){
			$row++;

			$this->objPHPExcel->setActiveSheetIndex($this->activeSheetIndex)
	            ->setCellValue('A'. $row, $singleQuestion['ID'])
	            ->setCellValue('B'. $row, $singleQuestion['post_title'])
	            ->setCellValue('C'. $row, (isset($singleQuestion['skip']))?'Skipped':'Not answered');
		}
		$this->objPHPExcel->getActiveSheet()->setTitle('Question Response');
		$this->activeSheetIndex++;
	}

	private function MobilizationSheet()
	{
		$mobilizationSheet = new PHPExcel_Worksheet($this->objPHPExcel, 'Mobilization');
		// Attach the “My Data” worksheet as the first worksheet in the PHPExcel object
		$this->objPHPExcel->addSheet($mobilizationSheet, $this->activeSheetIndex);
		$questionsWithNotes = $this->QuestionnaireController->getQuestionsWithNotes($this->currentUserQuestionnaireTree);
		$row = 1;
		$this->objPHPExcel->setActiveSheetIndex($this->activeSheetIndex)
	            ->setCellValue('A'. $row, 'ID')
	            ->setCellValue('B'. $row, 'Title')
	            ->setCellValue('C'. $row, 'Value')
	            ->setCellValue('D'. $row, 'Note');
		$this->objPHPExcel->setActiveSheetIndex($this->activeSheetIndex)->getStyle('A1:D1')->applyFromArray($this->headerStyle);
		foreach($questionsWithNotes as $key => $singleQuestion){
			$row++;
			$this->objPHPExcel->setActiveSheetIndex($this->activeSheetIndex)
	            ->setCellValue('A'. $row, $singleQuestion['ID'])
	            ->setCellValue('B'. $row, $singleQuestion['post_title'])
	            ->setCellValue('C'. $row, $singleQuestion['value'])
	            ->setCellValue('D'. $row, $singleQuestion['additional_note']);
		}
		$this->objPHPExcel->getActiveSheet()->setTitle('Mobilization');
		$this->activeSheetIndex++;
	}
}