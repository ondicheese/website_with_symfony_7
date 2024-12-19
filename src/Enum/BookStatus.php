<?php
namespace App\Enum;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

enum BookStatus: string implements TranslatableInterface
{
    case Available = 'available';
    case Borrowed = 'borrowed';
    case Unavailable = 'unavailable';
    
    public function getLabel(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::Borrowed => 'Emprunté',
            self::Unavailable => 'Indisponible',
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        // Translate enum from name (Left, Center or Right)
        //return $translator->trans($this->name, locale: $locale);

        // Translate enum using custom labels
        return match ($this) {
            self::Available  => $translator->trans('Disponible', locale: $locale),
            self::Borrowed => $translator->trans('Emprunté', locale: $locale),
            self::Unavailable  => $translator->trans('Indisponible', locale: $locale),
        };
    }
}