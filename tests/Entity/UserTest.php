<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $user = new User();

        // Test setUsername and getUsername
        $user->setUsername('testuser');
        $this->assertEquals('testuser', $user->getUsername());

        // Test setEmail and getEmail
        $user->setEmail('test@example.com');
        $this->assertEquals('test@example.com', $user->getEmail());

        // Test setUserIdentifier
        $this->assertEquals('test@example.com', $user->getUserIdentifier());

        // Test setRoles and getRoles
        $roles = ['ROLE_USER', 'ROLE_ADMIN'];
        $user->setRoles($roles);
        $this->assertEquals($roles, $user->getRoles());

        // Test setPassword and getPassword
        $password = 'password123';
        $user->setPassword($password);
        $this->assertEquals($password, $user->getPassword());

        // Test isVerified and setIsVerified
        $user->setIsVerified(true);
        $this->assertTrue($user->isVerified());

        // Test setEmailVerificationToken and getEmailVerificationToken
        $token = 'token123';
        $user->setEmailVerificationToken($token);
        $this->assertEquals($token, $user->getEmailVerificationToken());

        // Test setPasswordResetToken and getPasswordResetToken
        $resetToken = 'resettoken123';
        $user->setPasswordResetToken($resetToken);
        $this->assertEquals($resetToken, $user->getPasswordResetToken());
    }

    public function testAddAndRemoveTrick()
    {
        $user = new User();
        $trick = new Trick();

        // Test addTrick
        $user->addTrick($trick);
        $this->assertTrue($user->getTricks()->contains($trick));
        $this->assertSame($user, $trick->getAuthor());

        // Test removeTrick
        $user->removeTrick($trick);
        $this->assertFalse($user->getTricks()->contains($trick));
        $this->assertNull($trick->getAuthor());
    }

    public function testAddAndRemoveComment()
    {
        $user = new User();
        $comment = new Comment();

        // Test addComment
        $user->addComment($comment);
        $this->assertTrue($user->getComments()->contains($comment));

        // Test removeComment
        $user->removeComment($comment);
        $this->assertFalse($user->getComments()->contains($comment));
    }

    public function testStringRepresentation()
    {
        $user = new User();

        $this->assertEquals("Anonymous", (string) $user);

        $username = 'testuser';
        $user->setUsername($username);

        // Test __toString
        $this->assertEquals($username, (string) $user);
    }
}
