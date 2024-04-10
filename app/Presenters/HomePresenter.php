<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Utils\DateTime;
use Nette\Utils\Json;


final class HomePresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private Nette\Database\Explorer $database,
    ) {
        $record = $this->database->table('record')->limit(1);

        if (!count($record)) {
            $this->syncChuckJokes();
        }
    }

    private function syncChuckJokes()
    {
        $json = file_get_contents('https://www.digilabs.cz/hiring/data.php');
        $records = Json::decode($json);

        foreach ($records as $record) {
            $record->createdAt = DateTime::from($record->createdAt);

            $this->database->table('record')->insert((array) $record);
        }
    }

    public function renderMeme(): void
    {
        $records = $this->database->table('record')
            ->where('CHAR_LENGTH(joke) < ?', 120)
            ->order('RAND()')
            ->limit(1);

        $header = '';
        $footer = '';

        foreach ($records as $record) {
            $parts = explode(' ', $record->joke);
            $count = count($parts) / 2;

            foreach ($parts as $key => $part) {
                if ($key < $count) {
                    $header .= $part . ' ';
                } else {
                    $footer .= $part . ' ';
                }
            }
        }

        $this->template->header = $header;
        $this->template->footer = $footer;
    }

    public function renderNames(): void
    {
        $records = $this->database->table('record')
            ->where('SUBSTRING(SUBSTRING_INDEX(`name`, " ", 1), 1, 1) = SUBSTRING(SUBSTRING_INDEX(`name`, " ", -1), 1, 1)');

        $this->template->records = $records;
    }

    public function renderCalculation(): void
    {
        $records = $this->database->table('record')
            ->where('(`firstNumber` / `secondNumber`) = `thirdNumber`')
            ->where('MOD(`thirdNumber`, 2) = 0');

        $this->template->records = $records;
    }

    public function renderCreated(): void
    {
        $records = $this->database->table('record')
            ->where('createdAt > (DATE_SUB(CURDATE(), INTERVAL 1 MONTH))')
            ->where('createdAt < (DATE_SUB(CURDATE(), INTERVAL -1 MONTH))');

        foreach ($records as $record) {
            //var_dump($record->name);
        }

        $this->template->records = $records;
    }

    public function renderCalc2(): void
    {
        $records = $this->database->table('record');

        foreach ($records as $record) {

        }

        $this->template->records = $records;
    }

    public function handleModal($id)
    {
        var_dump($this->isAjax());
        if ($this->isAjax()) {
            $this->payload->isModal = TRUE;
            $this->redrawControl("modal");
        }
    }
}
