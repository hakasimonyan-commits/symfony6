<?php

namespace App\Entity;
// Entity-ների namespace-ը (Symfony convention)

use App\Repository\UserRepository;
// User-ի repository-ն (DB հարցումներ)

use Doctrine\ORM\Mapping as ORM;
// Doctrine ORM attributes

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
// Password ունեցող user-ի contract

use Symfony\Component\Security\Core\User\UserInterface;
// Symfony security user-ի հիմնական interface-ը

#[ORM\Entity(repositoryClass: UserRepository::class)]
// Ասում ենք՝ սա Doctrine entity է և ունի UserRepository

#[ORM\Table(
    name: 'user',
    // Տվյալների բազայում table-ի անունը

    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: 'UNIQ_IDENTIFIER_EMAIL',
            // constraint-ի անունը DB-ում
            fields: ['email']
            // email-ը unique է (երկու user նույն email-ով չի կարող լինել)
        )
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
// User class-ը պարտադիր է implements անի այս երկուսը authentication-ի համար
{
    #[ORM\Id]
    // primary key

    #[ORM\GeneratedValue]
    // auto increment

    #[ORM\Column]
    // սովորական column
    private ?int $id = null;
    // user-ի id-ն

    #[ORM\Column(length: 180)]
    // email column, max 180 char
    private ?string $email = null;

    #[ORM\Column]
    // roles-ը array է DB-ում (JSON)
    private array $roles = [];

    #[ORM\Column]
    // hashed password
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    // user-ի ազգանունը
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    // user-ի անունը
    private ?string $prenom = null;

    // -------- GETTERS & SETTERS --------

    public function getId(): ?int
    {
        return $this->id;
        // վերադարձնում է user-ի id-ն
    }

    public function getEmail(): ?string
    {
        return $this->email;
        // վերադարձնում է email-ը
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        // դնում է email-ը
        return $this;
    }

    public function getUserIdentifier(): string
    {
        // Symfony Security-ի համար user-ի "unique identifier"-ը
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // վերցնում ենք roles-ները DB-ից

        $roles[] = 'ROLE_USER';
        // պարտադիր role բոլոր user-ների համար

        return array_unique($roles);
        // կրկնվող role-ները մաքրում ենք
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        // roles-ը գրում ենք
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
        // վերադարձնում է hashed password-ը
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        // password-ը պետք է արդեն HASHED լինի
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
        // վերադարձնում է ազգանունը
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        // դնում է ազգանունը
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
        // վերադարձնում է անունը
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        // դնում է անունը
        return $this;
    }

    public function __serialize(): array
    {
        // session-ում իրական password-ը չպահելու համար
        $data = (array) $this;

        // password-ը փոխարինում ենք hash-ով
        $data["\0" . self::class . "\0password"] = hash(
            'crc32c',
            $this->password
        );

        return $data;
    }
}
