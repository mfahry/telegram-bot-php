<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
  <head>
  	<meta charset="utf-8">
  	<meta http-equiv="refresh" content="60">
  	<title>Telegram Bot</title>
  	<link rel="stylesheet" type="text/css" href="<?php echo base_url($css.'bootstrap.min.css')?>">
  	<link rel="stylesheet" type="text/css" href="<?php echo base_url($css.'bootstrap-theme.min.css')?>">
    <style>
      .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        padding-top: 100px; /* Location of the box */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.9); /* Black w/ opacity */
      }

      .modal-content {
        margin: auto;
        display: block;
        width: 80%;
        max-width: 700px;
      }

      .modal-content, #caption {
        -webkit-animation-name: zoom;
        -webkit-animation-duration: 0.6s;
        animation-name: zoom;
        animation-duration: 0.6s;
      }

      @-webkit-keyframes zoom {
        from {-webkit-transform:scale(0)}
        to {-webkit-transform:scale(1)}
      }

      @keyframes zoom {
        from {transform:scale(0)}
        to {transform:scale(1)}
      }

      /* The Close Button */
      .close {
        position: absolute;
        top: 15px;
        right: 35px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        transition: 0.3s;
      }

      .close:hover,
      .close:focus {
        color: #bbb;
        text-decoration: none;
        cursor: pointer;
      }

      /* 100% Image Width on Smaller Screens */
      @media only screen and (max-width: 700px){
          .modal-content {
              width: 100%;
          }
      }
    </style>
  </head>
  <body>
    <div style="margin:10px auto; width:1024px;">
      <div class="panel panel-success">
        <div class="panel-heading">
          <h5>Kumpulan File dari Tiket ID #<?php echo $ticket_id; ?></h5>
        </div>
        <div class="panel-body">
          <div class="row">
            <?php
            foreach($file as $row) {
              $explode_filename = explode(".",$row["FILE_NAME"]);
              $extension = $explode_filename[count($explode_filename) - 1]; ?>
              <div class="col-lg-3 col-md-3">
                <div class="thumbnail">
                  <?php
                  if(in_array($extension, array('png', 'jpg', 'jpeg'), true)){ ?>
                    <img src="<?php echo base_url("public/".$row["FILE_NAME"])?>" style="height: 250px; width: 80%; display: block; cursor:pointer;" class="img"/>
                  <?php
                  }
                  else {?>
                    <a href="<?php echo base_url("public/".$row["FILE_NAME"])?>" target="_blank" >
                      <img src=<?php echo base_url("assets/download.jpg")?> style="height: 250px; width: 80%; display: block; cursor:pointer;">
                    </a>
                  <?php
                  } ?>
                  <div class="caption text-center">
                    Dikirim pada <?php echo $row["MESSAGE_DATE"]; ?>
                  </div>
                </div>
              </div>
            <?php
            } ?>
          </div>
        </div>
      </div>
    </div>
    <!-- The Modal -->
    <div id="myModal" class="modal">

      <!-- The Close Button -->
      <span class="close" onclick="document.getElementById('myModal').style.display='none'">&times;</span>

      <!-- Modal Content (The Image) -->
      <img class="modal-content" id="img01">

      <!-- Modal Caption (Image Text) -->
      <div id="caption"></div>
    </div>
  </body>
  <footer>
    <script type="text/javascript" src="<?php echo base_url($js.'jquery-3.2.1.min.js'); ?>"></script>
  	<script type="text/javascript">
      $(document).ready(function(){
        $(".thumbnail .img").click(function(){
          // Get the modal
          var modal = $('#myModal');

          // Get the image and insert it inside the modal - use its "alt" text as a caption
          var img = $(this);
          var modalImg = $("#img01");
          var captionText = $("#caption");

          modal.attr("style", "display:block;");
          modalImg.attr("src", img.attr("src"));

        });
        $(".close").click(function(){
          $("#myModal").css("display","none");
        });
      });
    </script>
  </footer>
</html>
