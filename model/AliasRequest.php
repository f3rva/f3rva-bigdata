<?php
namespace F3\Model;

class AliasRequest {
  private Member $primaryMember;
  private Member $aliasMember;
  private AliasRequestStatus $status;

  /**
   * Summary of __construct
   * @param \F3\Model\Member $primaryMember
   * @param \F3\Model\Member $aliasMember
   * @param \F3\Model\AliasRequestStatus $status
   */
  public function __construct(Member $primaryMember, Member $aliasMember, AliasRequestStatus $status) {
    $this->primaryMember = $primaryMember;
    $this->aliasMember = $aliasMember;
    $this->status = $status;
  }

  /**
   * Summary of getPrimaryMember
   * @return \F3\Model\Member
   */
  public function getPrimaryMember(): Member {
    return $this->primaryMember;
  }

  /**
   * Summary of setPrimaryMember
   * @param \F3\Model\Member $primaryMember
   * @return void
   */
  public function setPrimaryMember(Member $primaryMember): void {
    $this->primaryMember = $primaryMember;
  }

  /**
   * Summary of getAliasMember
   * @return \F3\Model\Member
   */
  public function getAliasMember(): Member {
    return $this->aliasMember;
  }

  /**
   * Summary of setAliasMember
   * @param \F3\Model\Member $aliasMember
   * @return void
   */
  public function setAliasMember(Member $aliasMember): void {
    $this->aliasMember = $aliasMember;
  }

  /**
   * Summary of getStatus
   * @return \F3\Model\AliasRequestStatus
   */
  public function getStatus(): AliasRequestStatus {
    return $this->status;
  }

  /**
   * Summary of setStatus
   * @param \F3\Model\AliasRequestStatus $status
   * @return void
   */
  public function setStatus(AliasRequestStatus $status): void {
    $this->status = $status;
  }
}

