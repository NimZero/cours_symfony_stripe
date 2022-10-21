<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SubscriptionVoter extends Voter implements CacheableVoterInterface
{
    public const PREFIX = 'SUBSCRIPTION_TIER_';

    private array $subscriptionProducts;

    public function __construct(array $subscriptionProducts)
    {
        $this->subscriptionProducts = $subscriptionProducts;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return str_starts_with($attribute, SELF::PREFIX)
            && ($subject === null || $subject instanceof User);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User */
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        $tier = strtolower(substr($attribute, strlen(SELF::PREFIX)));

        if (array_key_exists($tier, $this->subscriptionProducts)) {
            $userTier = array_search($user->getSubscriptionProduct(), $this->subscriptionProducts);

            return $userTier >= $tier;
        }

        return false;
    }

    public function supportsAttribute(string $attribute): bool
    {
        return str_starts_with($attribute, SELF::PREFIX);
    }

    /**
     * @param string $subjectType The type of the subject inferred by `get_class` or `get_debug_type`
     */
    public function supportsType(string $subjectType): bool
    {
        return $subjectType === 'null' || $subjectType === User::class;
    }
}
