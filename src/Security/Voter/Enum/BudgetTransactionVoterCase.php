<?php

namespace App\Security\Voter\Enum;

use App\Trait\EnumToArray;

enum BudgetTransactionVoterCase: string
{
    use EnumToArray;

    case View = "BUDGET_TRANSACTION_VIEW";
    case Create = "BUDGET_TRANSACTION_CREATE";
}
