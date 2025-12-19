<?php
  class Display {
    var $settings;
    var $page_html;
    function load_settings() {
      global $db;
    
      $settings = [];
$settingsData = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
foreach ($settingsData as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}
      $this->settings = $settings;
    }
    function output($footer) {
      global $settings;
      if(!$settings) {
        $settings = $this->settings;
      }
      
      
      $html = "";
      $html .= $this->page_html;
      if($footer) {
        $year = date("Y");
        $site_name = $settings['site_name'];
        $html .= "<footer class='bg-dark text-white text-center py-4 mt-5'><div class='container'><p>".date("Y")." ".
          " ". htmlspecialchars($settings['site_name'] ?? 'My Portfolio') .". All rights reserved.</p><div class='social-links mt-3'>";
        if(!empty($settings['social_twitch'])) {
          $html .= "<a href='".htmlspecialchars($settings['social_twitch'])."' class='text-white me-3'><i class='bi bi-twitch'></i></a>";
        }
        if(!empty($settings['social_linkedin'])) {
          $html .= "<a href='".htmlspecialchars($settings['social_linkedin'])."' class='text-white me-3'>LinkedIn</a>";
        }
        if(!empty($settings['social_github'])) {
          $html .= "<a href='".htmlspecialchars($settings['social_github'])."' class='text-white me-3'><i class='bi bi-github'></i></a>";
        }
        if(!empty($settings['social_twitter'])) {
          $html .= "<a href='".htmlspecialchars($settings['social_twitter'])."' class='text-white me-3'>Twitter</i></a>";
        }
        $html ."</div></div></div></footer>";
      }
      $html .= "</div><script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js\"></script></body></html>";
      echo $html;
    }
  }
?>
