<?php

namespace App\Security\Voter\Enum;

enum BudgetVoterCase: string
{
    case View= "BUDGET_VIEW";
    case Delete = "BUDGET_DELETE";
    case Update = "BUDGET_UPDATE";
}

