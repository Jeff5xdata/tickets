<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Determine whether the user can view any tickets.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the ticket.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->user_id;
    }

    /**
     * Determine whether the user can create tickets.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the ticket.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->user_id;
    }

    /**
     * Determine whether the user can delete the ticket.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->user_id;
    }

    /**
     * Determine whether the user can delete multiple models.
     */
    public function deleteAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the ticket.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->user_id;
    }

    /**
     * Determine whether the user can permanently delete the ticket.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->user_id;
    }
} 