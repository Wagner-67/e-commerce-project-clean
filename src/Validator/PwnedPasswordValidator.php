<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PwnedPasswordValidator extends ConstraintValidator
{
    public function __construct(private HttpClientInterface $client) {}

    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        $hash = strtoupper(sha1($value));
        $prefix = substr($hash, 0, 5);
        $suffix = substr($hash, 5);

        try {
            $response = $this->client->request('GET', "https://api.pwnedpasswords.com/range/$prefix");
            $body = $response->getContent();

            foreach (explode("\n", $body) as $line) {
                [$hashSuffix, $count] = array_map('trim', explode(':', $line));
                if ($hashSuffix === $suffix && (int) $count > 0) {
                    $this->context->buildViolation($constraint->message)
                        ->addViolation();
                    return;
                }
            }
        } catch (\Throwable $e) {

        }
    }
}
