<?php

class SonarQubeClientTest extends PHPUnit_Framework_TestCase {

  private $sonarHost = 'sonar.mycompany.com';
  private $assignerUsername = 'sonaradmin';
  private $assignerPassword = 'password';
  
  private $sonarQubeClient;
  private $project = 'com.tomslabs.tools:sonar-review-creator';
  private $severities = 'BLOCKER,CRITICAL,MAJOR';
  private $depth = '-1';
  
  private $projectViolationsJson;
  
  public function setUp() {
    $this->sonarQubeClient = new SonarQubeClient($this->sonarHost, $this->assignerUsername, $this->assignerPassword);
    $this->projectViolationsJson = $this->readJsonResultFromFile('_fixtures/projectViolationsJson.json');
  }
  
  private function readJsonResultFromFile($fileName) {
    $file = dirname(__FILE__).'/'.$fileName;
    return file_get_contents($file);  
  }    
  
  /** @test */
  public function buildGetViolationsUrl() {
    assertThat($this->sonarQubeClient->buildGetViolationsUrl($this->project, $this->depth, $this->severities), 
      equalTo("http://sonar.mycompany.com/api/issues/search?componentRoots=com.tomslabs.tools:sonar-review-creator&severities=BLOCKER,CRITICAL,MAJOR"));
  }  
  
  /** @test */
  public function readFirstViolationFromStubResponse() {
    $sonarQubeClient = $this->mockSonarQubeClient();
    $violations = $sonarQubeClient->getViolations($this->project, $this->depth, $this->severities);
    
    $firstViolation = $violations['issues'][13];
    
    $violationLineNumber = $firstViolation['line'];
    $violatedFile = $firstViolation['component'];
    
    $explodeViolatedFile = explode(':', $violatedFile);
    $violatedFullFilePath = array_pop($explodeViolatedFile);
    
    assertThat($violations["paging"]["total"], equalTo(14));
    assertThat($violationLineNumber, equalTo(71));
    assertThat($violatedFile, equalTo("com.purch.fe:community-comments-bundle:karma.conf.js"));
    assertThat($violatedFullFilePath, equalTo("karma.conf.js"));
  }  
  
  private function mockSonarQubeClient() {
    $sonarQubeClient = $this->getMock('SonarQubeClient', array('getViolations'), array($this->project, $this->depth, $this->severities), '', true);
    $sonarQubeClient->expects($this->once())
                    ->method('getViolations')
                    ->will($this->returnValue(json_decode($this->projectViolationsJson, true)));    
    return $sonarQubeClient;
  }
  
}