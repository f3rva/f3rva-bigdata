<?php

namespace F3\Model;

enum AliasRequestStatus: string {
  case PENDING = 'pending';
  case APPROVED = 'approved';
  case REJECTED = 'rejected';

  public static function enumFrom(string $value): ?self {
    return match($value) {
      self::PENDING->value => self::PENDING,
      self::APPROVED->value => self::APPROVED,
      self::REJECTED->value => self::REJECTED,
      default => null,
    };
  }
}