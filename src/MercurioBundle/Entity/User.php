<?php

namespace MercurioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="MercurioBundle\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Este endereço de e-mail já está em uso.")
 * @todo Incluir atividades do usuario
 */
class User implements AdvancedUserInterface, \Serializable
{

    /**
     * @ORM\Id;
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=40)
     * @Assert\Regex(
     *     pattern="/\s/",
     *     message="Digite seu nome completo"
     * )
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $role;

    /**
     * @Assert\Length(
     *      min = 6,
     *      max = 32,
     *      minMessage = "Sua senha deve possuir no mínimo {{ limit }} caracteres",
     *      maxMessage = "Sua senha deve possuir no máximo {{ limit }} caracteres"
     * )
     */
    protected $plainPassword;

    /**
     * @ORM\Column(type="string", length=64)
     */
    protected $password;

    /**
     * @ORM\Column(type="boolean") 
     */
    protected $status = false;

    /**
     * @ORM\Column(type="string", nullable=true) 
     */
    protected $code;

    public function eraseCredentials()
    {
        return null;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role = null)
    {
        $this->role = $role;
    }

    public function getRoles()
    {
        return [$this->getRole()];
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = ucwords($name);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    public function getSalt()
    {
        return null;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatusStr()
    {
        return ($this->status) ? 'Ativado' : 'Desativado';
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->status;
    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->email,
            $this->name,
            $this->role,
            $this->plainPassword,
            $this->password,
            $this->status,
            $this->code
        ]);
    }

    public function unserialize($serialized)
    {
        list(
                $this->id,
                $this->email,
                $this->name,
                $this->role,
                $this->plainPassword,
                $this->password,
                $this->status,
                $this->code
                ) = unserialize($serialized);
    }

}
