<?php

namespace App\Models\Contracts;

/**
 * A model with an ordered tech stack (see HasSkills).
 */
interface HasStack
{
    /**
     * @param  list<int>  $skillIds
     */
    public function syncSkillsInOrder(array $skillIds): void;
}
