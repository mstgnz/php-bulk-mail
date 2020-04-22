<?php

public function toplumail(){
		//Delete
		$getParams = $this->request->query;
		if (!empty($getParams['del'])){
			$id = $getParams['del'];
			$this->db->query("DELETE FROM emailing_templates WHERE template_id='$id' AND company_id = '$this->companyId'");
			$this->Session->setFlash($id.' nolu Template Silindi.', 'flash_success');
			$this->redirect('/admin/toplumail');
		}
		// Template List
		$templates = $this->db->query("SELECT * FROM emailing_templates et WHERE company_id=$this->companyId");
		$template = [];
		foreach ($templates as $k => $t){
			array_push($template, ["id"=>$t['et']['template_id'], "title"=>$t['et']['template_name'], "description"=>$t['et']['template_text'], "content"=>html_entity_decode($t['et']['template_html'], ENT_QUOTES,"UTF-8")]);
		} 
		$this->set('template', json_encode($template));
		$this->set('templates', $templates);
		// Mail List
		$mails = $this->db->query("SELECT contact_email FROM companies ce WHERE service_type='active'");
		$mailList = [];
		foreach ($mails as $key => $value) {
			if($value['ce']['contact_email']!="" && filter_var($value['ce']['contact_email'], FILTER_VALIDATE_EMAIL)){
				array_push($mailList, trim($value['ce']['contact_email']));
			}
		}
		$mailList =  array_values(array_unique($mailList));
		$this->set('mails', $mailList);
		// Post
		if($this->request->is("POST")){
			$data = $this->request->data;
			// Mail Send
			if($data['type'] && $data['type']=="send"){
				if(isset($data['subject']) && $data['subject']!="" && isset($data['content']) && $data['content']!=""){
					foreach ($data['to'] as $key => $value) {
						if(!empty($value) && filter_var($value, FILTER_VALIDATE_EMAIL)){
							$this->Emails->sendAll($data['from'], $value, $data['subject'], $data['content']);
						}
					}
					$this->Session->setFlash('Toplu Mail Gönderimi Tamamlanmıştır.', 'flash_success');
					$this->redirect('/admin/toplumail');
				}else{
					$this->Session->setFlash('Mail konusu ve Mail içeriği boş olamaz.', 'flash_error');
					$this->redirect('/admin/toplumail');
				}
			}
			// Template Save
			if($data['type'] && $data['type']=="save"){
				if($data['theme']!="" && $data['name']!="" && $data['desc']!=""){
					$theme_name = $data['name'];
					$theme_desc = $data['desc'];
					$theme_code = trim($data['theme']);
					$theme_code = stripslashes($theme_code);
					$theme_code = htmlentities(htmlspecialchars($theme_code), ENT_QUOTES, "UTF-8");
					//echo $theme_code;exit;
					if(isset($data['id']) && is_numeric($data['id'])){
						$theme_id = $data['id'];
						$this->db->query("UPDATE emailing_templates SET template_name='$theme_name', template_text='$theme_desc', template_html='$theme_code' WHERE template_id = $theme_id");
						$this->Session->setFlash($theme_id. ' nolu tema güncellenmiştir.', 'flash_success');
					}else{
						$this->EmailingTemplate->create();
						$theme = array(
								"company_id"			=> $this->companyId,
								"template_name"			=> $theme_name,
								"template_html"			=> $theme_code,
								"template_text"			=> $theme_desc,
								"template_date_create" 	=> date("Y-m-d H:i:s")
						);
						$this->EmailingTemplate->save($theme);
						$this->Session->setFlash($theme_name. ' isimli temanız kaydedilmiştir.', 'flash_success');
					}
					$this->redirect('/admin/toplumail');
				}else{
					$this->Session->setFlash('Lütfen Boş Alan Bırakmayınız!', 'flash_error');
					$this->redirect('/admin/toplumail');
				}
			}
			// Get Mail List
			if($data['type'] && $data['type']=="ajax"){
				$this->autoRender = false;
				$mailType= $data['mailtype'];
				$mails = $this->db->query("SELECT contact_email FROM companies ce WHERE service_type='$mailType'");
				$mailList = [];
				foreach ($mails as $key => $value) {
					if($value['ce']['contact_email']!="" && filter_var($value['ce']['contact_email'], FILTER_VALIDATE_EMAIL)){
						array_push($mailList, trim($value['ce']['contact_email']));
					}
				}
				$mailList =  array_values(array_unique($mailList));
				echo json_encode($mailList);
			}
		}
	}

?>
