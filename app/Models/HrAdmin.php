<?php

namespace App\Models;

use Parental\HasParent;

/**
 * Class HrAdmin
 * 
 * Represents an HR admin.
 * 
 * @package App\Models
 * @version 1.0
 * @since 28-04-2026
 * @author Abdelhalim Yasser
 */
class HrAdmin extends AbstractInterviewer
{
    use HasParent;
}
