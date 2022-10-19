<?php

namespace App\Security\Voter\Enum;

use App\Trait\EnumToArray;

enum BudgetVoterCase: string
{
    use EnumToArray;

    case View= "BUDGET_VIEW";
    case Delete = "BUDGET_DELETE";
    case Update = "BUDGET_UPDATE";
}
