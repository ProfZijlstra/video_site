<?php

/**
 * @author mzijlstra 14 Jan 2024
 */

#[Repository]
class SubmissionDao
{
    #[Inject('DB')]
    public $db;
}
