<?php
defined("BASEPATH") OR exit("No direct script access allowed");

class Auth extends CI_Controller {
  private $json = null;

  function __construct(){
    parent::__construct();
    $this->_getData();
  }

  function getToken(){
    $data = array(
      "username" => $this->json["username"],
      "password" => md5($this->json["password"])
    );

    $this->load->model("auth_model");
    $auth = $this->auth_model->get($data);
    if($auth != null){
      // generate token (datetime + user + 'B0t!@#')
      $token = md5(date('YmdHis').$auth->USERNAME."B0t!@#");
      $where = array(
        "ip" => $this->input->ip_address()
      );

      // check last auth request to database
      $this->load->model("auth_request_model");
      if($this->auth_request_model->get($where) != null){
        $data = array(
          "token" => $token
        );

        $this->auth_request_model->change($data, $where);
      }
      else {
        $data = array(
          "token" => $token,
          "ip" => $this->input->ip_address(),
        );
        $this->auth_request_model->store($data);
      }

      $result = array("result" => array(
        "token" => $token,
        "ip" => $this->input->ip_address(),
        "success" => "Request is success"
      ));
    }
    else {
      $result = array("result" => array("error" => "Get Token Failed"));
    }

    echo json_encode($result);
  }

  function closeTicket(){
    $token = $this->json["token"];
    $ip =  $this->input->ip_address();
    if($this->_validAuth($token, $ip) != null){
      // update status tiket = SOLVED
      $this->load->model("telegram_message_model");
      $where = array(
        "ticket_id" => $this->json["ticketID"]
      );

      $data = array(
        "ticket_solution" => $this->json["solution"],
        "ticket_status" => "SOLVED"
      );

      // if update status ticket is success
      if($this->telegram_message_model->change($data, $where)){
        $this->load->library("telegram", array("bot_id" => "331710692:AAGLqH4Yidz7ifiho9EM_y_2xPNfrK3Z-08"));

        // get chat id, message id, ticket id, ticket solution, ticket message
        $where = array(
          "ticket_id" => $this->json["ticketID"]
        );

        $telegram_message = $this->telegram_message_model->get($where);

        if($telegram_message != null){
          // send reply to user
          $content = array(
  					"chat_id" => $telegram_message->CHAT_ID,
  					"text" => "Komplain anda dgn ticket ID #".$telegram_message->TICKET_ID." telah selesai.".chr(10)."<b>Permasalahan</b>: ".$telegram_message->MESSAGE.chr(10).chr(10)."<b>Solusi:</b> ".$telegram_message->TICKET_SOLUTION,
  					"reply_to_message_id" => $telegram_message->MESSAGE_ID,
            "parse_mode" => "HTML"
          );
          $this->telegram->sendMessage($content);

          // delete data from user_accepted_request
          $this->load->model("user_accepted_request_model");
          $where = array(
            "ticket_id" => $telegram_message->TICKET_ID,
            "username_id" => $telegram_message->USERNAME_ID
          );

          $this->user_accepted_request_model->destroy($where);

          //endpushchat
          $this->load->model("pushchat_model");
          $this->pushchat_model->destroy($where);

          $result = array("result" => array("success" => "Close ticket is successed"));
        }
        else {
          $result = array("result" => array("error" => "Close ticket is failed"));
        }
      }
      else {
        $result = array("result" => array("error" => "Close ticket is failed"));
      }
    }
    else {
      $result = array("result" => array("error" => "Authentication is invalid"));
    }
    echo json_encode($result);
  }

  function replyTicket(){
    $token = $this->json["token"];
    $ip =  $this->input->ip_address();
    if($this->_validAuth($token, $ip) != null){
      $ticketID = $this->json["ticketID"];

      // get chat id, message id, ticket id
      $this->load->model("telegram_message_model");

      $where = array(
        "ticket_id" => $this->json["ticketID"]
      );
      $telegram_message = $this->telegram_message_model->get($where);

      if($telegram_message != null) {
        $this->load->library("telegram", array("bot_id" => "331710692:AAGLqH4Yidz7ifiho9EM_y_2xPNfrK3Z-08"));

        // send message or photo to user
        if($this->json["type"] == "text") {
          $content = array(
            "chat_id" => $telegram_message->CHAT_ID,
            "text" => $this->json["text"],
            "reply_to_message_id" => $telegram_message->MESSAGE_ID,
            "parse_mode" => "HTML"
          );
          $this->telegram->sendMessage($content);
        }
        else {
          //get file to local
          $name_file = $this->json["text"];
          copy("http://172.18.44.227/portalmac/photo/".$name_file, "internal/".$name_file);

          $content = array(
            "chat_id" => $telegram_message->CHAT_ID,
            "photo" => "https://bot.bri.co.id/customercare/internal/".$name_file,
            "reply_to_message_id" => $telegram_message->MESSAGE_ID,
          );
          $this->telegram->sendPhoto($content);
        }

        $result = array("result" => array("success" => "Send message to telegram is success"));
      }
      else {
        $result = array("result" => array("error" => "Send message to telegram is failed"));
      }
    }
    else {
      $result = array("result" => array("error" => "Authentication is invalid"));
    }
    echo json_encode($result);
  }

  function _validAuth($token, $ip){
    $where = array(
      "token" => $token,
      "ip" => $ip
    );

    $this->load->model("auth_request_model");
    return $this->auth_request_model->get($where);
  }

  function _getData(){
    if($this->json == null) {
      $content = file_get_contents("php://input");
      $this->json = json_decode($content, true);
    }
  }

}
