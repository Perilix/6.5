<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email;

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'author')]
    private Collection $posts;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'author')]
    private Collection $comments;

    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * @var Collection<int, PostFeedback>
     */
    #[ORM\OneToMany(targetEntity: PostFeedback::class, mappedBy: 'user')]
    private Collection $postFeedback;

    /**
     * @var Collection<int, CommentFeedback>
     */
    #[ORM\OneToMany(targetEntity: CommentFeedback::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $commentFeedback;

    #[ORM\Column]
    private ?bool $banned = false;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->postFeedback = new ArrayCollection();
        $this->commentFeedback = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        if ($isVerified) {
            $this->addRole('ROLE_USER_REGISTERED');
        }

        return $this;
    }

    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @return Collection<int, PostFeedback>
     */
    public function getPostFeedback(): Collection
    {
        return $this->postFeedback;
    }

    public function addPostFeedback(PostFeedback $postFeedback): static
    {
        if (!$this->postFeedback->contains($postFeedback)) {
            $this->postFeedback->add($postFeedback);
            $postFeedback->setUser($this);
        }

        return $this;
    }

    public function removePostFeedback(PostFeedback $postFeedback): static
    {
        if ($this->postFeedback->removeElement($postFeedback)) {
            // set the owning side to null (unless already changed)
            if ($postFeedback->getUser() === $this) {
                $postFeedback->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommentFeedback>
     */
    public function getCommentFeedback(): Collection
    {
        return $this->commentFeedback;
    }

    public function addCommentFeedback(CommentFeedback $commentFeedback): static
    {
        if (!$this->commentFeedback->contains($commentFeedback)) {
            $this->commentFeedback->add($commentFeedback);
            $commentFeedback->setUser($this);
        }

        return $this;
    }

    public function removeCommentFeedback(CommentFeedback $commentFeedback): static
    {
        if ($this->commentFeedback->removeElement($commentFeedback)) {
            // set the owning side to null (unless already changed)
            if ($commentFeedback->getUser() === $this) {
                $commentFeedback->setUser(null);
            }
        }

        return $this;
    }

    public function isBanned(): ?bool
    {
        return $this->banned;
    }

    public function setBanned(bool $banned): self
    {
        $this->banned = $banned;

        return $this;
    }
}
