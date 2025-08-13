<?php

namespace App\Policies;

use App\Models\User;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Contracts\Commenter;
use Illuminate\Auth\Access\Response;
use Kirschbaum\Commentions\Policies\CommentPolicy as CommentionsPolicy;

class CommentPolicy extends CommentionsPolicy
{

    public function create(Commenter $user): bool
    {
         return true;
    }

    public function update($user, Comment $comment): bool
    {
        return false;
    }

    public function delete($user, Comment $comment): bool
    {
        return false;
    }

}
