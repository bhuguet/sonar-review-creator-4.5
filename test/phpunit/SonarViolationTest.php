<?php

class SonarViolationTest extends PHPUnit_Framework_TestCase {

  private $sonarQubeClient;
  private $sonarViolation;
  private $phpStdClassViolation;
  
  public function setUp() {
    $this->sonarQubeClient = $this->getMock('SonarQubeClient', array('executeCreateReview'), array(), '', false);
    $this->phpStdClassViolation = $this->newPhpStdClassViolation();
    $this->sonarViolation = new SonarViolation($this->sonarQubeClient, $this->phpStdClassViolation);
  }
  
  /** @test */
  public function phpSonarViolationAttributes() {
    $violation = $this->phpStdClassViolation;
    assertThat($this->sonarViolation->getId(), equalTo($violation->key));
    assertThat($this->sonarViolation->getLineNumber(), equalTo($violation->line));
    assertThat($this->sonarViolation->getFileFullKey(), equalTo($violation->component));
  }
  
  private function newPhpStdClassViolation() {
    $violation = (object) array(
         "key" => "7de73395-b8ed-4b76-abf6-f039ed647caf",
         "component" => "com.tomslabs.tools:sonar-review-creator:User/UserClientService.php",
         "componentId" => 24485,
         "project" => "com.tomslabs.tools:sonar-review-creator",
         "rule" => "php:S1788",
         "status" => "OPEN",
         "severity" => "CRITICAL",
         "message" => "Move arguments serviceConfig after arguments without default value",
         "line" => 40,
         "debt" => "20min",
         "creationDate" => "2015-05-20T12:06:12+0000",
         "updateDate" => "2015-05-20T12:06:12+0000",
         "fUpdateAge" => "a day"
    );
    return $violation;
  }

  /** @test */
  public function javaSonarViolationAttributes() {
    $violation = $this->newJavaStdClassViolation();
    $sonarViolation = new SonarViolation($this->sonarQubeClient, $violation);

    assertThat($sonarViolation->getId(), equalTo($violation->key));
    assertThat($sonarViolation->getLineNumber(), equalTo($violation->line));
    assertThat($sonarViolation->getFileFullKey(), equalTo($violation->component));
  }

  private function newJavaStdClassViolation() {
    $violation = (object) array(
         "key" => "d9b00605-324b-4cf5-9e18-37d478d9d645",
         "component" => "com.bom.be:bss-media-comments-core:src/main/java/com/bom/be/core/util/JaxbWrapper.java",
         "componentId" => 20880,
         "project" => "com.bom.be:bss-media-comments",
         "rule" => "squid:AvoidContinueStatement",
         "status" => "CLOSED",
         "resolution" => "REMOVED",
         "severity" => "MAJOR",
         "message" => "The 'continue' branching statement prevent refactoring the source code to reduce the complexity.",
         "line" => 236,
         "creationDate" => "2014-12-02T10:14:54+0000",
         "updateDate" => "2015-01-07T14:05:05+0000",
         "fUpdateAge" => "4 months",
         "closeDate" => "2015-01-07T14:05:05+0000"
      );
      return $violation;
  }

  /** @test */
  public function return1WhenLineNumberIsMissing() {
    $violation = $this->newPhpStdClassViolationWithoutLineNumber();
    $sonarViolation = new SonarViolation($this->sonarQubeClient, $violation);

    assertThat($sonarViolation->getId(), equalTo($violation->key));
    assertThat($sonarViolation->getLineNumber(), equalTo(1));
    assertThat($sonarViolation->getFileFullKey(), equalTo($violation->component));
  }

  private function newPhpStdClassViolationWithoutLineNumber() {
    $violation = (object) array(
         "key" => "d7b571fd-51f7-4c3e-9ebe-e9ad582a8397",
         "component" => "com.tomslabs.tools:sonar-review-creator:Controller/AlertController.php",
         "componentId" => 24430,
         "project" => "com.tomslabs.tools:sonar-review-creator",
         "rule" => "php:S1451",
         "status" => "OPEN",
         "severity" => "BLOCKER",
         "message" => "Add or update the header of this file.",
         "debt" => "5min",
         "creationDate" => "2015-05-20T09:16:49+0000",
         "updateDate" => "2015-05-20T09:16:49+0000",
         "fUpdateAge" => "a day"
      );
      return $violation;
  }

  /** @test */
  public function findPathToPhpViolatedFile() {
    $sourceDirectory = "/home/tomslabs/workspace/sonar-review-creator/app";
    $sonarViolation = $this->getMock('SonarViolation', array('find', 'changeToDirectory'), array(), '', false);
    $sonarViolation->expects($this->once())
                   ->method('find')
                   ->will($this->returnValue(array('./modules/thirdParty/actions/components.class.php')));     
    $sonarViolation->computeFileNameFullPath($sourceDirectory);
    assertThat($sonarViolation->getFileNameFullPath(), equalTo('./modules/thirdParty/actions/components.class.php'));
  }

  /** @test */
  public function findPathToJavaViolatedFile() {
    $sourceDirectory = "/home/tomslabs/worspace/javaApp";
    $sonarViolation = $this->getMock('SonarViolation', array('find', 'changeToDirectory'), array(), '', false);
    $sonarViolation->expects($this->once())
        ->method('find')
        ->will($this->returnValue(array('./webservice/src/main/java/com/bom/tomslabs/javaApp/appController.java')));
    $sonarViolation->computeFileNameFullPath($sourceDirectory);
    assertThat($sonarViolation->getFileNameFullPath(), equalTo('./webservice/src/main/java/com/bom/tomslabs/javaApp/appController.java'));
  }

    /** @test */
  public function getAnnotationFromViolatedFileAndLineNumber() {
    $sourceDirectory = "/home/tomslabs/workspace/sonar-review-creator/app";
    $sonarViolation = $this->mockSonarViolationToExecGitBlameAndStubLdapMatcher();
    $sonarViolation->computeAssignee($sourceDirectory, 'git');
    assertThat($sonarViolation->getAssignee(), equalTo('smartin'));
  }
  
  private function mockSonarViolationToExecGitBlameAndStubLdapMatcher() {
    $sonarViolation = $this->getMock('SonarViolation', array('executeGitBlameCommand'), array(), '', false);
    $sonarViolation->expects($this->once())
                   ->method('executeGitBlameCommand')
                   ->will($this->returnValue('4463b322 (Sébastien M 2013-10-23 10:49:10 +0200 249)   private static function getMarkupWhenLayer($page, $pageQuantity, $maxPageFirstLine, $route, $routeOptions, $pagerId, $fi'));    
    
    $ldapUserAliasesMatcher = $this->getMock('LdapUserAliasesMatcher', array('getLdapAliasesFile'));
    $ldapUserAliasesMatcher->expects($this->once())
                           ->method('getLdapAliasesFile')
                           ->will($this->returnValue(dirname(__FILE__).'/_fixtures/ldap-aliases-for-tests.json'));    
    
    $sonarViolation->setLdapUserAliasesMatcher($ldapUserAliasesMatcher);
    return $sonarViolation;
  }
  
  /** @test */
  public function getDeveloperFromGitBlameOutput() {
    $blameOutput = "4463b322 (Sébastien M 2013-10-23 10:49:10 +0200 249)   private static function getMarkupWhenLayer(";
    
    $assignee = $this->sonarViolation->extractDeveloperFromGitBlameOutput($blameOutput);
    assertThat($assignee, equalTo("Sébastien M"));
  }

  /** @test */
  public function getDeveloperFromSvnBlameOutput() {
    $blameOutput = " 26965    smartin     long nextLong = abs(random.nextLong());";
    $assignee = $this->sonarViolation->extractDeveloperFromSvnBlameOutput($blameOutput);
    assertThat($assignee, equalTo("smartin"));
  }
  
  /** @test */
  public function createReviewForPickedViolation() {
    $this->sonarViolation->setAssignee('Bernard');
    $this->sonarViolation->createReview();
  }
  
}
