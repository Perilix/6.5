<?php

namespace App\Entity;

use App\Repository\PostRepository;

use App\Utils\HtmlTruncation;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', cascade: ['remove'], orphanRemoval: true)]
    private Collection $comments;

    /**
     * @var Collection<int, PostFeedback>
     */
    #[ORM\OneToMany(targetEntity: PostFeedback::class, mappedBy: 'post', cascade: ['remove'], orphanRemoval: true)]
    private Collection $postFeedback;

    private ?HtmlTruncation $htmlTruncation = null;

    public function __construct(HtmlTruncation $htmlTruncation)
    {
        $this->htmlTruncation = $htmlTruncation;
        $this->comments = new ArrayCollection();
        $this->postFeedback = new ArrayCollection();
    }

    public function setHtmlTruncation(HtmlTruncation $htmlTruncation): void
    {
        $this->htmlTruncation = $htmlTruncation;
    }

    public function getTruncatedContent($length = 200, $ending = '...')
    {
        return $this->htmlTruncation->truncate($this->content, $length, $ending);
    }

    #[ORM\PrePersist]
    public function initializeCreatedAt(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new DateTimeImmutable();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

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
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
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
            $postFeedback->setPost($this);
        }

        return $this;
    }

    public function removePostFeedback(PostFeedback $postFeedback): static
    {
        if ($this->postFeedback->removeElement($postFeedback)) {
            // set the owning side to null (unless already changed)
            if ($postFeedback->getPost() === $this) {
                $postFeedback->setPost(null);
            }
        }

        return $this;
    }

    public function countLikes(): int
    {
        return $this->postFeedback->filter(function(PostFeedback $feedback) {
            return $feedback->getType() === 'like';
        })->count();
    }

    public function countDislikes(): int
    {
        return $this->postFeedback->filter(function(PostFeedback $feedback) {
            return $feedback->getType() === 'dislike';
        })->count();
    }

    public function getLikes(): int
    {
        return $this->countLikes();
    }

    public function getDislikes(): int
    {
        return $this->countDislikes();
    }


}
