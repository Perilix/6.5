<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?Post $post = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    /**
     * @var Collection<int, CommentFeedback>
     */
    #[ORM\OneToMany(targetEntity: CommentFeedback::class, mappedBy: 'comment', orphanRemoval: true)]
    private Collection $commentFeedback;

    /**
     * @var Collection<int, CommentReport>
     */
    #[ORM\OneToMany(targetEntity: CommentReport::class, mappedBy: 'comment', orphanRemoval: true)]
    private Collection $commentReports;

    public function __construct()
    {
        $this->commentFeedback = new ArrayCollection();
        $this->commentReports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAt(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new DateTimeImmutable();
        }
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

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
            $commentFeedback->setComment($this);
        }

        return $this;
    }

    public function removeCommentFeedback(CommentFeedback $commentFeedback): static
    {
        if ($this->commentFeedback->removeElement($commentFeedback)) {
            // set the owning side to null (unless already changed)
            if ($commentFeedback->getComment() === $this) {
                $commentFeedback->setComment(null);
            }
        }

        return $this;
    }

    public function countLikes(): int
    {
        return $this->commentFeedback->filter(function(CommentFeedback $feedback) {
            return $feedback->getType() === 'like';
        })->count();
    }

    public function countDislikes(): int
    {
        return $this->commentFeedback->filter(function(CommentFeedback $feedback) {
            return $feedback->getType() === 'dislike';
        })->count();
    }

    /**
     * @return Collection<int, CommentReport>
     */
    public function getCommentReports(): Collection
    {
        return $this->commentReports;
    }

    public function addCommentReport(CommentReport $commentReport): static
    {
        if (!$this->commentReports->contains($commentReport)) {
            $this->commentReports->add($commentReport);
            $commentReport->setComment($this);
        }

        return $this;
    }

    public function removeCommentReport(CommentReport $commentReport): static
    {
        if ($this->commentReports->removeElement($commentReport)) {
            // set the owning side to null (unless already changed)
            if ($commentReport->getComment() === $this) {
                $commentReport->setComment(null);
            }
        }

        return $this;
    }
}
