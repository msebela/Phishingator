<?php
  use PHPUnit\Framework\TestCase;

  require_once '../globalFunctions.php';

  final class CredentialsTesterModelTest extends TestCase {
    public function testTryLogin() {
      $result = CredentialsTesterModel::tryLogin(TEST_USERNAME, TEST_PASSWORD);

      $this->assertTrue($result);
    }
  }