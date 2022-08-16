<?php

namespace App\Models;


use Illuminate\Support\Facades\Mail;

class Invite extends Model
{
    protected $fillable = ['email', 'role'];

    /**
     *
     * Метод вернет объект инвайта по коду и только если инвайт не использован
     * Иначе вернет NULL
     *
     * @param string $code
     */
    public static function getInviteByCode($code) {
        return Invite::where('code', $code)->where('claimed', NULL)->first();
    }

    /**
     * Генератор рандомного кода инвайта
     */
    protected function generateInviteCode() {
        $this->code = bin2hex(openssl_random_pseudo_bytes(16));
    }

    /**
     * Метод отправляет инвайт по почте
     *
     * @param string $message_text - текст сообщения
     */
    public function sendInvitation($message_text) {
        $inviter = User::find($this->inviter_id);
        $params = [
            'inviter' => $inviter->name,
            'message_text' => (!empty($message_text)) ? $message_text : '',
            'code' => $this->code,
        ];
        Mail::send('emails.invite', $params, function ($message) {
            $message->to($this->email)->subject('Invite to site');
        });
    }

    /**
     *
     * Связываем модель инвайта с моделью пользователя, отправляющего приглашение
     * Связь через поле inviter_id
     */
    public function inviter() {
        return $this->belongsTo('App\Models\User', 'inviter_id');
    }

    /**
     *
     * Связываем модель инвайта с моделью пользователя, получившего приглашение
     * Может пригодиться, если захотим показывать, кто кого пригласил
     * Связь через поле invitee_id
     */
    public function invitee() {
        return $this->belongsTo('App\Models\User', 'invitee_id');
    }

    /**
     * Используем метод boot() чтобы подключиться к событию создания модели
     * будем генерировать код инвайта сразу при создании
     */
    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            $model->generateInviteCode();
        });
    }
}
