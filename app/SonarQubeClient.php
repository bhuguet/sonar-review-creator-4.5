<?php

class SonarQubeClient {
  
  private $sonarHost;
  private $assignerUsername;
  private $assignerPassword;
  
  public function __construct($sonarHost, $assignerUsername, $assignerPassword) {
    $this->sonarHost = $sonarHost;
    $this->assignerUsername = $assignerUsername;
    $this->assignerPassword = $assignerPassword;
  }
  
  public function getViolations($project, $severities) {
    $url = $this->buildGetViolationsUrl($project, $severities);
    echo "\ngetViolations($project, $severities)\n";
    echo $url;
    return json_decode($this->executeGet($url), true);
  }
  
  public function buildGetViolationsUrl($project, $severities) {
    return "http://".$this->sonarHost."/api/issues/search?componentRoots=".$project."&severities=".$severities;
  }  
  
  protected function executeGet($url)
  {
    $ch = curl_init();
    $output = null;
    try
    {
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//      curl_setopt($ch, CURLOPT_USERPWD, self::CURL_TOKENIZED_CREDENTIALS);
      $output = curl_exec($ch);
    }
    catch(Exception $e)
    {
      echo $e->getMessage();
    }
    curl_close($ch);
    return $output;
  }   
  
  public function executeCreateReview($violationId, $assignee) {
    $url = $this->buildCreateReviewUrl($violationId, $assignee);

    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, count(array()));
    curl_setopt($ch,CURLOPT_POSTFIELDS, '');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $this->assignerUsername . ':' . $this->assignerPassword);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    //execute post
    $result = curl_exec($ch);
    var_dump($result);

    //close connection
    curl_close($ch);    
  }

  public function buildCreateReviewUrl($violationId, $assignee) {
    return 'http://' . $this->sonarHost . '/api/issues/assign?issue='.$violationId.'&assignee='.$assignee;
  }
  
}
