<?php

/**
 * @see https://github.com/barbushin/php-imap
 * @author Julian Stricker based on Barbushin Sergey http://linkedin.com/in/barbushin
 *
 */

namespace XxamMailclientBundle\Helper\Imap;

use PhpImap\IncomingMail;
use PhpImap\IncomingMailAttachment;

class ImapMailbox extends \PhpImap\Mailbox{

    /**
     * Fetch mail headers for listed mails ids
     *
     * Returns an array of objects describing one mail header each. The object will only define a property if it exists. The possible properties are:
     *  subject - the mails subject
     *  from - who sent it
     *  to - recipient
     *  date - when was it sent
     *  message_id - Mail-ID
     *  references - is a reference to this mail id
     *  in_reply_to - is a reply to this mail id
     *  size - size in bytes
     *  uid - UID the mail has in the mailbox
     *  msgno - mail sequence number in the mailbox
     *  recent - this mail is flagged as recent
     *  flagged - this mail is flagged
     *  answered - this mail is flagged as answered
     *  deleted - this mail is flagged for deletion
     *  seen - this mail is flagged as already read
     *  draft - this mail is flagged as being a draft
     *
     * @param array $mailsIds
     * @return array
     */
    public function getMailsInfo(array $mailsIds) {
        $mails = imap_fetch_overview($this->getImapStream(), implode(',', $mailsIds), FT_UID);
        if (is_array($mails) && count($mails)) {
            foreach ($mails as &$mail) {
                if (isset($mail->subject)) {
                    $mail->subject = $this->decodeMimeStr($mail->subject, $this->serverEncoding);
                }
                if (isset($mail->from)) {
                    $mail->from = $this->decodeMimeStr($mail->from, $this->serverEncoding);
                }
                if (isset($mail->to)) {
                    $mail->to = $this->decodeMimeStr($mail->to, $this->serverEncoding);
                }
                
                $mail->hasattachment=$this->emailHasAttachments($mail->uid);
            }
        }
        return $mails;
    }

    /**
     * Gets mails ids sorted by some criteria
     *
     * Criteria can be one (and only one) of the following constants:
     *  SORTDATE - mail Date
     *  SORTARRIVAL - arrival date (default)
     *  SORTFROM - mailbox in first From address
     *  SORTSUBJECT - mail subject
     *  SORTTO - mailbox in first To address
     *  SORTCC - mailbox in first cc address
     *  SORTSIZE - size of mail in octets
     *
     * * @param string $searchcriteria String, delimited by spaces, in which the following keywords are allowed. Any multi-word arguments (e.g. FROM "joey smith") must be quoted. Results will match all criteria entries.
     *    ALL - return all mails matching the rest of the criteria
     *    ANSWERED - match mails with the \\ANSWERED flag set
     *    BCC "string" - match mails with "string" in the Bcc: field
     *    BEFORE "date" - match mails with Date: before "date"
     *    BODY "string" - match mails with "string" in the body of the mail
     *    CC "string" - match mails with "string" in the Cc: field
     *    DELETED - match deleted mails
     *    FLAGGED - match mails with the \\FLAGGED (sometimes referred to as Important or Urgent) flag set
     *    FROM "string" - match mails with "string" in the From: field
     *    KEYWORD "string" - match mails with "string" as a keyword
     *    NEW - match new mails
     *    OLD - match old mails
     *    ON "date" - match mails with Date: matching "date"
     *    RECENT - match mails with the \\RECENT flag set
     *    SEEN - match mails that have been read (the \\SEEN flag is set)
     *    SINCE "date" - match mails with Date: after "date"
     *    SUBJECT "string" - match mails with "string" in the Subject:
     *    TEXT "string" - match mails with text "string"
     *    TO "string" - match mails with "string" in the To:
     *    UNANSWERED - match mails that have not been answered
     *    UNDELETED - match mails that are not deleted
     *    UNFLAGGED - match mails that are not flagged
     *    UNKEYWORD "string" - match mails that do not have the keyword "string"
     *    UNSEEN - match mails which have not been read yet
     *
     * @param int $criteria
     * @param bool $reverse
     * @return array Mails ids
     */
    public function sortMails($criteria = SORTARRIVAL, $reverse = true, $searchcriteria = null) {
        return imap_sort($this->getImapStream(), $criteria, $reverse, SE_UID, $searchcriteria, $this->serverEncoding);
    }


    /*
     * add a Swiftmailer Email to Folder
     * Julian Stricker
     * 
     */

    public function addMail($msg, $seen = true) {
        return imap_append($this->getImapStream(), $this->imapPath, $msg . "\r\n", $seen ? "\\Seen" : null);
    }
    
    /*
     * Fast way to check if Email has attachments
     * Julian Stricker
     * 
     */
     public function emailHasAttachments($mailId){
        $mailStructure = imap_fetchstructure($this->getImapStream(), $mailId, FT_UID);
        if (empty($mailStructure->parts)) {
            return false;
        } else {
            foreach ($mailStructure->parts as $partNum => $partStructure) {
                if (property_exists($partStructure, 'disposition') && strtoupper($partStructure->disposition)=='ATTACHMENT') return true;
            }
        }
        return false;
    }

}
