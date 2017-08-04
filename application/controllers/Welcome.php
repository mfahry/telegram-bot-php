<?php
defined("BASEPATH") OR exit("No direct script access allowed");

class Welcome extends CI_Controller {

	public function __construct() {
		parent::__construct();
	}

	public function webhook() {
		$this->load->library("telegram", array("bot_id" => "331710692:AAGLqH4Yidz7ifiho9EM_y_2xPNfrK3Z-08"));

		// load all model
		$this->load->model("telegram_message_model");
		$this->load->model("pushchat_model");
		$this->load->model("user_accepted_request_model");
		$this->load->model("master_mac_model");
		$this->load->model("history_chat_model");

		// parse data from telegram
		$update_id = $this->telegram->UpdateID();
		$message_id = $this->telegram->MessageID();
		$username_id = $this->telegram->UserID();
		$username = $this->telegram->Username();
		$firstname = $this->telegram->FirstName();
		$lastname = $this->telegram->LastName();
		$fullname = $firstname.' '.$lastname;

		$chat_id = $this->telegram->ChatID();
		$chat_group_name = $this->telegram->messageFromGroupTitle();
		$message_date = date("Y-m-d H:i:s", $this->telegram->Date());

		if($usename != "TSIMAC") {
			if($this->telegram->Text() != "") {

				$message = $this->telegram->Text();

				//	parsing message with general format "/request spasi kode_uker atau TID ATM#nama uker#lokasi#Nama PIC#No HP PIC#Problem"
				$parsed_message = explode("#", $message);
				$command_checker = explode(" ",$parsed_message[0]);

				if($command_checker[0] == "/request" || $command_checker[0] == "/request@customer_care_bot") {
					//	check error format
					if(count($parsed_message) != 6) {
						//	send message correction format
						$content = array(
							"chat_id" => $chat_id,
							"text" => "mohon gunakan format yang sesuai /request spasi kode_uker atau TID ATM#nama uker#lokasi#Nama PIC#No HP PIC#Problem",
							"reply_to_message_id" => $message_id
						);

						$this->telegram->sendMessage($content);
					}
					else if(! is_numeric($command_checker[1])) {
						//	send message correction kode uker
						$content = array(
							"chat_id" => $chat_id,
							"text" => "mohon kode uker atau TID ATM harus angka",
							"reply_to_message_id" => $message_id
						);

						$this->telegram->sendMessage($content);
					}
					else {
						$tt_kd_uker = $command_checker[1];
						$tt_nm_uker = $parsed_message[1]." ".$parsed_message[2];
						$tt_pic_nm= $parsed_message[3];
						$tt_pic_telp = $parsed_message[4];
						$tt_problem = $parsed_message[5];

						//	check what user id have quota to request ticket
						$where = array(
							"username_id" => $username_id
						);
						$user_accepted_request = $this->user_accepted_request_model->getList($where);

						if(count($user_accepted_request) < 5){
							// check if text message already exist in table
							$where = array(
								"message" => $message
							);

							$telegram_message = $this->telegram_message_model->get($where);
							if($telegram_message == null) {
								// insert data to portal MAC
								$data = array(
									"tt_kd_uker" => $tt_kd_uker,
									"tt_nm_uker" => $tt_nm_uker,
									"tt_pic_nm" => $tt_pic_nm,
									"tt_pic_telp" => $tt_pic_telp,
									"tt_problem" => $tt_problem,
									"tt_inputer" => "mac_bot",
									"tt_id_disp" => "MAC",
									"tt_id_status" => 2,
									"tt_tgl" => $message_date
								);

								$ticket_id = $this->master_mac_model->store($data);

								// insert data to history chat
								$data = array(
									"tt_id" => $ticket_id,
									"tt_text" => $tt_problem,
									"tt_date" => $message_date,
									"tt_type_chat" => "text"
								);

								$this->history_chat_model->store($data);

								//	save telegram message
								$data = array(
									"update_id" => $update_id,
									"message_id" => $message_id,
									"username_id" => $username_id,
									"username" => $username,
									"fullname" => $fullname,
									"chat_id" => $chat_id,
									"chat_group_name" => $chat_group_name,
									"message" => $message,
									"message_date" => $message_date,
									"ticket_id" => $ticket_id
								);

								$this->telegram_message_model->store($data);

								// insert data to user_accepted_request
								$data = array(
									"username_id" => $username_id,
									"ticket_id" => $ticket_id,
									"quota_description" => 0
								);

								$this->user_accepted_request_model->store($data);

								//	reply message
								$content = array(
									"chat_id" => $chat_id,
									"text" => "Terima kasih, permintaan anda sudah dalam antrian dengan TICKET ID ".$ticket_id.
										chr(10).chr(10).
										"Apabila dibutuhkan untuk mengirimkan dokumen, gambar atau penjelasan pendukung, silahkan menggunakan fitur <b>pushchat</b> dengan format ".
										chr(10).chr(10).
										"/pushchat spasi <code>nomor tiket</code>".
										chr(10).chr(10).
										"Lakukan chat seperti biasa setelah mengaktifkan fitur tersebut. Setelah selesai mengirimkan penjelasan harap menonaktifkan fitur pushchat dengan format ".
										chr(10).chr(10).
										"/endpushchat".
										chr(10).chr(10).
										"<b>Untuk mengetahui progress tiket</b>".chr(10)."Pesan harus menggunakan format".
										chr(10).chr(10).
										"/ticketinfo spasi <code>nomor tiket</code>".
										chr(10).chr(10).
										"<b>Untuk mengetahui semua tiket anda dengan status OPEN</b>".chr(10)."Silahkan menggunakan command <code>/status</code>",
									"reply_to_message_id" => $message_id,
									"parse_mode" => "HTML"
								);

								$this->telegram->sendMessage($content);

								// endpushchat
								$where = array(
									"username_id" => $username_id
								);

								$this->pushchat_model->destroy($where);

							}
						}
						else {
							$ticket_ids = "";
							$i = 0;
							foreach($user_accepted_request as $row) {
								if($i == 0){
									$ticket_ids = $row->TICKET_ID;
									$i=1;
								}
								else {
									$ticket_ids.= ", ".$row->TICKET_ID;
								}
							}

							//	reply message
							$content = array(
								"chat_id" => $chat_id,
								"text" => "Maaf, jumlah tiket dengan status OPEN sudah maksimal dengan nomor ticket ".$ticket_ids.". Maksimal tiket terbuka adalah 5".chr(10).chr(10).
												"Silahkan hapus salah satu tiket dengan menggunakan ".chr(10).chr(10)."/closeticket spasi <code>nomor ticket</code>",
								"reply_to_message_id" => $message_id,
								"parse_mode" => "HTML"
							);
							$this->telegram->sendMessage($content);
						}
					}
				}
				else if($command_checker[0] == "/help" || $command_checker[0] == "/help@customer_care_bot") {

					//	send message help
					$content = array(
						"chat_id" => $this->telegram->ChatID(),
						"text" => "<b>Selamat datang di BOT Telegram Divisi OPT</b>".chr(10)."Kami akan membantu anda untuk lebih memudahkan dalam melakukan permintaan atau komplain".
							chr(10).chr(10).
							"<b>Untuk mengirimkan request</b> ".chr(10)."1. Isi pesan harus dengan format ".
							chr(10).chr(10).
							"/request spasi <code>kode uker atau TID ATM#nama uker#lokasi#Nama PIC#No HP PIC#Problem</code>".
							chr(10).chr(10).
							"2. Apabila dibutuhkan untuk mengirimkan dokumen, gambar atau penjelasan pendukung, silahkan menggunakan fitur <b>pushchat</b> dengan format ".
							chr(10).chr(10).
							"/pushchat spasi <code>nomor tiket</code>".
							chr(10).chr(10).
							"Lakukan chat seperti biasa setelah mengaktifkan fitur tersebut. Setelah selesai mengirimkan penjelasan harap menonaktifkan fitur pushchat dengan format ".
							chr(10).chr(10).
							"/endpushchat".
							chr(10).chr(10).
							"3. Jangan menggunakan caption pada gambar/dokumen".chr(10)."4. Maksimal Open Tiket untuk 1 user adalah 5 tiket".
							chr(10).chr(10).
							"<b>Untuk mengetahui progress tiket</b>".chr(10)."Pesan harus menggunakan format".
							chr(10).chr(10).
							"/ticketinfo spasi <code>nomor tiket</code>".
							chr(10).chr(10).
							"<b>Untuk mengetahui semua tiket anda dengan status OPEN</b>".chr(10)."Silahkan menggunakan command".
							chr(10).chr(10).
							"<code>/status</code>".
							chr(10).chr(10).
							"Terima kasih atas kepedulian anda terhadap apa yang terjadi di lapangan untuk kemajuan BRI. Semoga BOT ini dapat membantu anda dalam mengirimkan request atau menlihat status request. Diharapkan kritik dan saran terhadap BOT ini.".chr(10)."<b>Divisi Operasional Teknologi Informasi</b>",
						"parse_mode" => "HTML"
					);
					$this->telegram->sendMessage($content);
				}

				else if($command_checker[0] == "/ticketinfo" || $command_checker[0] == "/ticketinfo@customer_care_bot") {
					if (count($command_checker) == 2) {
						$ticket_id = $command_checker[1];

						$where = array(
							"ticket_id" => $ticket_id
						);

						$telegram_message = $this->telegram_message_model->get($where);
						if($telegram_message == null){
							$content = array(
								"chat_id" => $this->telegram->ChatID(),
								"text" => "Maaf nomor ticket tidak ditemukan.",
								"reply_to_message_id" => $message_id,
								"parse_mode" => "HTML"
							);

							$this->telegram->sendMessage($content);
						}
						else {
							$content = array(
								"chat_id" => $this->telegram->ChatID(),
								"text" => "Ticket ID : ".$telegram_message->TICKET_ID.chr(10)."Dari : ".$telegram_message->FULLNAME."(@".$telegram_message->USERNAME.")".chr(10)."Status : ".$telegram_message->TICKET_STATUS.chr(10).chr(10)."Permasalahan : ".$telegram_message->MESSAGE.chr(10).chr(10)."Disposisi ke: ".$telegram_message->DISPOSITION_GROUP_CODE,
								"reply_to_message_id" => $message_id,
								"parse_mode" => "HTML"
							);

							$this->telegram->sendMessage($content);
						}
					}
					else {
						$content = array(
							"chat_id" => $this->telegram->ChatID(),
							"text" => "Pesan harus menggunakan format".
								chr(10).chr(10).
								"/ticketinfo spasi <code>nomor tiket</code>",
							"reply_to_message_id" => $message_id,
							"parse_mode" => "HTML"
						);

						$this->telegram->sendMessage($content);
					}
				}
				else if($command_checker[0] == "/pushchat" || $command_checker[0] == "/pushchat@customer_care_bot") {
					//check what text format is valid
					if(count($command_checker) == 2) {
						$ticket_id = $command_checker[1];

						// get data from user_accepted_request
						$where = array(
							"ticket_id" => $ticket_id,
							"username_id" => $username_id
						);

						$user_accepted_request = $this->user_accepted_request_model->get($where);

						//if user_accepted_request is exist
						if($user_accepted_request != null){
							$where = array(
								"username_id" => $username_id
							);

							// delete pushchat by username id
							$this->pushchat_model->destroy($where);

							$data = array(
								"ticket_id" => $ticket_id,
								"username_id" => $username_id
							);

							$this->pushchat_model->store($data);

							$content = array(
								"chat_id" => $chat_id,
								"text" => "Pushchat berhasil diaktifkan untuk nomor tiket ".$ticket_id,
								"reply_to_message_id" => $message_id,
								"parse_mode" => "HTML"
							);
							$this->telegram->sendMessage($content);
						}
						else {
							//	reply error message
							$content = array(
								"chat_id" => $chat_id,
								"text" => "Maaf nomor tiket tidak ada atau tiket sudah ditutup / solved",
								"reply_to_message_id" => $message_id
							);
							$this->telegram->sendMessage($content);
						}
					}
					else {
						//	reply error message
						$content = array(
							"chat_id" => $chat_id,
							"text" => "Maaf format teks tidak sesuai. Silahkan menggunakan format /pushchat spasi <code>nomor ticket</code>",
							"reply_to_message_id" => $message_id,
							"parse_mode" => "HTML"
						);
						$this->telegram->sendMessage($content);
					}
				}
				else if($command_checker[0] == "/endpushchat" || $command_checker[0] == "/endpushchat@customer_care_bot") {
					//check what this user still have active pushchat
					$where = array(
						"username_id" => $username_id
					);

					$pushchat = $this->pushchat_model->get($where);

					if($pushchat != null){
						$this->pushchat_model->destroy($where);

						// reply message
						$content = array(
							"chat_id" => $chat_id,
							"text" => "Pushchat berhasil dinonaktifkan",
							"reply_to_message_id" => $message_id
						);
						$this->telegram->sendMessage($content);
					}
				}
				else if($command_checker[0] == "/status" || $command_checker[0] == "/status@customer_care_bot") {
					// check status pushchat
					$where = array(
						"username_id" => $username_id
					);

					$pushchat = $this->pushchat_model->get($where);

					// generate message text
					$pushchat_status = $pushchat == null ? "tidak aktif" : "aktif untuk nomor tiket".$pushchat->TICKET_ID;
					$text = "Status Pushchat anda <b>".$pushchat_status."</b>".chr(10).chr(10)."Tiket dengan status OPEN terkait user anda :".chr(10).chr(10);

					//get open ticket data by username
					$where = array(
						"a.username_id" => $username_id,
						"b.ticket_status" => 'ON PROCESS'
					);

					$user_accepted_request = $this->user_accepted_request_model->getDetail($where);
					foreach($user_accepted_request as $row){
						$text.= "<b>Nomor Tiket : </b>".$row->TICKET_ID.chr(10);
						$text.= "<b>Permasalahan : </b>".$row->MESSAGE.chr(10).chr(10);
					}

					//	reply message
					$content = array(
						"chat_id" => $chat_id,
						"text" => $text,
						"reply_to_message_id" => $message_id,
						"parse_mode" => "HTML"
					);
					$this->telegram->sendMessage($content);
				}
				else if($command_checker[0] == "/closeticket" || $command_checker[0] == "/closeticket@customer_care_bot") {
					//check what text format is valid
					if(count($command_checker) == 2) {
						$ticket_id = $command_checker[1];

						// get data from user_accepted_request
						$where = array(
							"ticket_id" => $ticket_id,
							"username_id" => $username_id
						);

						$user_accepted_request = $this->user_accepted_request_model->get($where);

						//if user_accepted_request is exist
						if($user_accepted_request != null){
							$where = array(
								"username_id" => $username_id,
								"ticket_id" => $ticket_id,
								"ticket_status" => "ON PROCESS"
							);

							// delete telegram message by username id and ticket id
							$this->telegram_message_model->destroy($where);

							$where = array(
								"tt_id" => $ticket_id
							);

							$data = array(
								"tt_solusi" => "closed by ticket owner",
								"tt_id_status" => "3"
							);

							$this->master_mac_model->change($data, $where);

							$content = array(
								"chat_id" => $chat_id,
								"text" => "Nomor tiket ".$ticket_id." berhasil diclose oleh user",
								"reply_to_message_id" => $message_id,
								"parse_mode" => "HTML"
							);
							$this->telegram->sendMessage($content);
						}
						else {
							//	reply error message
							$content = array(
								"chat_id" => $chat_id,
								"text" => "Maaf, tiket sudah solved atau tiket ".$ticket_id." bukan milik anda",
								"reply_to_message_id" => $message_id
							);
							$this->telegram->sendMessage($content);
						}
					}
					else {
						//	reply error message
						$content = array(
							"chat_id" => $chat_id,
							"text" => "Maaf format teks tidak sesuai. Silahkan menggunakan format /closeticket spasi <code>nomor ticket</code>",
							"reply_to_message_id" => $message_id,
							"parse_mode" => "HTML"
						);
						$this->telegram->sendMessage($content);
					}
				}
				else {
					$where = array(
						"username_id" => $username_id
					);

					$user_accepted_request = $this->user_accepted_request_model->get($where);

					//check what this chat have to be recorded or not
					if($user_accepted_request != null) {

						//get pushchat data
						$where = array(
							"username_id" => $username_id
						);

						$pushchat = $this->pushchat_model->get($where);

						//using ticket_id in pushchat table if data exits
						if($pushchat != null){
							$ticket_id = $pushchat->TICKET_ID;

							// check quota allow chat
							$where = array(
								"username_id" => $username_id,
								"ticket_id" => $ticket_id
							);

							$user_accepted_request = $this->user_accepted_request_model->get($where);

							// insert data to history chat
							if($user_accepted_request->QUOTA_DESCRIPTION < 5) {
								$data = array(
									"tt_id" => $ticket_id,
									"tt_text" => $message,
									"tt_date" => $message_date,
									"tt_type_chat" => "text"
								);

								$this->history_chat_model->store($data);

								//update quota allow chat
								$data = array(
									"quota_description" => $user_accepted_request->QUOTA_DESCRIPTION + 1
								);

								$this->user_accepted_request_model->change($data, $where);

								//update is read in tt_table_mac
								$data = array(
									"is_read" => 1
								);

								$where = array(
									"tt_id" => $ticket_id
								);

								$this->master_mac_model->change($data, $where);
							}
							else {
								$content = array(
									"chat_id" => $chat_id,
									"text" => "Maaf, jumlah maksimal untuk mengirimkan deskripsi mengenai tiket adalah 5. Anda bisa kembali mengirim deskripsi apabila pihak MAC membalas tiket anda. Terima Kasih",
									"reply_to_message_id" => $message_id,
									"parse_mode" => "HTML"
								);
								$this->telegram->sendMessage($content);
							}
						}
						else {
							$content = array(
								"chat_id" => $chat_id,
								"text" => "Pushchat belum diaktifkan. Anda belum bisa kami layani. Silahkan mengaktifkan menggunakan format /pushchat spasi <code>nomor tiket</code> ",
								"reply_to_message_id" => $message_id,
								"parse_mode" => "HTML"
							);
							$this->telegram->sendMessage($content);
						}
					}
					else {
						$content = array(
							"chat_id" => $chat_id,
							"text" => "Mohon maaf, format tidak sesuai. Mohon untuk menggunakan format yang telah disediakan".chr(10).
								chr(10).chr(10).
								"<b>Untuk mengirimkan request</b> ".chr(10)."1. Isi pesan harus dengan format ".
								chr(10).chr(10).
								"/request spasi <code>kode uker atau TID ATM#nama uker#lokasi#Nama PIC#No HP PIC#Problem</code>".
								chr(10).chr(10).
								"Terima kasih atas kepedulian anda terhadap apa yang terjadi di lapangan untuk kemajuan BRI. Semoga BOT ini dapat membantu anda dalam mengirimkan request atau menlihat status request. Diharapkan kritik dan saran terhadap BOT ini.".chr(10)."<b>Divisi Operasional Teknologi Informasi</b>",
							"reply_to_message_id" => $message_id,
							"parse_mode" => "HTML"
						);

						$this->telegram->sendMessage($content);
					}
				}
			}
			else if($this->telegram->Photo() != "") {
				$where = array(
					"username_id" => $username_id
				);

				$user_accepted_request = $this->user_accepted_request_model->get($where);

				//check what this chat have to be recorded or not
				if($user_accepted_request != null) {
					$pushchat = $this->pushchat_model->get($where);

					//using ticket_id in pushchat table if data exits
					if($pushchat != null){
						$ticket_id = $pushchat->TICKET_ID;

						$photo = $this->telegram->Photo();

						$file_id = $photo[count($photo) - 1]["file_id"];

						$result_get_file = $this->telegram->getFile($file_id);
						$file_path = $result_get_file["result"]["file_path"];
						$file_path_explode = explode(".",$file_path);
						$file_temp_name = $file_path_explode[0];
						$extension = $file_path_explode[count($file_path_explode) - 1];

						//$file_name = date("YmdHis", $this->telegram->Date())."_".$username_id."_".$update_id.".".$extension;
						$file_name = date("YmdHis", $this->telegram->Date())."_".rand(1, rand()).".".$extension;
						$this->telegram->downloadFile($file_path, "public/".$file_name);

						// insert data to history chat
						$data = array(
							"tt_id" => $ticket_id,
							"tt_text" => "bot.bri.co.id/customercare/public/".$file_name,
							"tt_date" => $message_date,
							"tt_type_chat" => "image"
						);

						$this->history_chat_model->store($data);

						//update quota allow chat
						$data = array(
							"quota_description" => $user_accepted_request->QUOTA_DESCRIPTION + 1
						);

						$where = array(
							"username_id" => $username_id,
							"ticket_id" => $ticket_id
						);

						$this->user_accepted_request_model->change($data, $where);

						//update is read in tt_table_mac
						$data = array(
							"is_read" => 1
						);

						$where = array(
							"tt_id" => $ticket_id
						);

						$this->master_mac_model->change($data, $where);
					}
					else {
						$content = array(
							"chat_id" => $chat_id,
							"text" => "Pushchat belum diaktifkan. Anda belum bisa kami layani. Silahkan mengaktifkan menggunakan format /pushchat spasi <code>nomor tiket</code> ",
							"reply_to_message_id" => $message_id,
							"parse_mode" => "HTML"
						);

						$this->telegram->sendMessage($content);
					}
				}
			}
			else if($this->telegram->Document() != "") {
				$where = array(
					"username_id" => $username_id
				);

				$user_accepted_request = $this->user_accepted_request_model->get($where);

				//check what this chat have to be recorded or not
				if($user_accepted_request != null) {
					$pushchat = $this->pushchat_model->get($where);

					//using ticket_id in pushchat table if data exits
					if($pushchat != null){
						$ticket_id = $pushchat->TICKET_ID;

						$document = $this->telegram->Document();
						$file_id = $document["file_id"];

						$result_get_file = $this->telegram->getFile($file_id);
						$file_path = $result_get_file["result"]["file_path"];
						$file_path_explode = explode(".",$file_path);
						$file_temp_name = $file_path_explode[0];
						$extension = $file_path_explode[count($file_path_explode) - 1];

						$file_name = date("YmdHis", $this->telegram->Date())."_".rand(1, rand()).".".$extension;
						$this->telegram->downloadFile($file_path, "public/".$file_name);

						// insert data to history chat
						$data = array(
							"tt_id" => $ticket_id,
							"tt_text" => "bot.bri.co.id/customercare/public/".$file_name,
							"tt_date" => $message_date,
							"tt_type_chat" => "document"
						);

						$this->history_chat_model->store($data);

						//update quota allow chat
						$data = array(
							"quota_description" => $user_accepted_request->QUOTA_DESCRIPTION + 1
						);

						$where = array(
							"username_id" => $username_id,
							"ticket_id" => $ticket_id
						);

						$this->user_accepted_request_model->change($data, $where);

						//update is read in tt_table_mac
						$data = array(
							"is_read" => 1
						);

						$where = array(
							"tt_id" => $ticket_id
						);

						$this->master_mac_model->change($data, $where);
					}
					else {
						$content = array(
							"chat_id" => $chat_id,
							"text" => "Pushchat belum diaktifkan. Anda belum bisa kami layani. Silahkan mengaktifkan menggunakan format /pushchat spasi <code>nomor tiket</code> ",
							"reply_to_message_id" => $message_id,
							"parse_mode" => "HTML"
						);

						$this->telegram->sendMessage($content);
					}
				}
			}
		}
	}
}
