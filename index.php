<?php

$doc = new DOMDocument();
$xsl = new XSLTProcessor();

$doc->load(__DIR__ . '/leprogramme.xslt');
$xsl->importStyleSheet($doc);

$doc->load(__DIR__ . '/a.xml');
$data = $xsl->transformToXML($doc);
file_put_contents(__DIR__.'/result.html', $data);

$doc = new DOMDocument();
$doc->loadHTML($data);
$xpath = new DOMXPath($doc);

$chaines = array();
$domChannels = $xpath->query('/html/body/div');
foreach ($domChannels as $domChannel) {
    $channel = new Channel();
    $id = $domChannel->getAttribute('id');
    $channel->id = substr($id, strpos($id, '_')+1);
    $domChaine = $xpath->query('div[@class="chaine"]', $domChannel)->item(0);
    $channel->name = $domChaine->getAttribute('title');
    $domPrograms = $xpath->query('div[@class="programme"]/div[starts-with(@class, "emission")]/div/div[starts-with(@id, "data_")]', $domChaine);
    foreach ($domPrograms as $domEmission) {
        $program = new Program();
        $object = json_decode($domEmission->nodeValue);
        $program->fromObject($object);
        $channel->addProgram($program);
    }
    $chaines[] = $channel;
}
var_dump($chaines);

class Channel
{
    /**
     * @var integer
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var Program[]
     */
    public $programs = array();

    public function addProgram($program)
    {
        $this->programs[] = $program;
        return $this;
    }
}

class Program
{
    /**
     * @var Chaine
     */
    public $channel;
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $subTitre;
    /**
     * @var string
     */
    public $url;
    /**
     * @var string
     */
    public $showView;
    /**
     * Timestamp
     *
     * @var integer
     */
    public $start;
    /**
     * Timestamp
     *
     * @var integer
     */
    public $stop;
    /**
     * @var string
     */
    public $date;
    /**
     * @var string
     */
    public $desc;
    /**
     * @var Category
     */
    public $category;
    /**
     * @var string
     */
    public $language;
    /**
     * @var string
     */
    public $origLanguage;
    /**
     * @var Length
     */
    public $length;
    /**
     * @var string
     */
    public $country;
    /**
     * @var Episode
     */
    public $episode;
    /**
     * @var Video
     */
    public $video;
    /**
     * @var Audio
     */
    public $audio;
    /**
     * @var string
     */
    public $subtitles;
    /**
     * @var string
     */
    public $rating;
    /**
     * @var string
     */
    public $starRating;
    /**
     * @var Credits
     */
    public $credits;

    public function fromObject($object)
    {
        $this->id = $object->Id_Emission;
        $this->start = strtotime($object->Date_Debut);
        $this->stop = strtotime($object->Date_Fin);
        $this->title = $object->Titre;
        $this->url = 'http://television.telerama.fr/tele/programmes-tv/'
            . $object->Titre_Url . ',' . $this->id . '.php';
        $this->showView = $object->ShowView;
        $this->setStarRating($object->note_T);
        $this->setCategory($object->Type);
        $this->setLength($object->DureeEnSecondes, 'seconds');
        $this->setEpisode($object->episode_numero, $object->saison_numero);
        $this->desc = trim($object->resume_long);
        $this->setCredits($object->intervenant);
    }

    public function setStarRating($note)
    {
        if (empty($note)) {
            return;
        }
        $note = (int)$note;
        if ($note == 1) {
            $this->starRating = '2/5';
        } elseif ($note == 2) {
            $this->starRating = '3/5';
        } elseif ($note == 3) {
            $this->starRating = '4/5';
        } elseif ($note == 5) {
            $this->starRating = '1/5';
        } elseif ($note > 5) {
            $this->starRating = '0/5';
        }
    }

    public function setCategory($type)
    {
        $this->category = new Category();
        $this->category->name = $type;
        $this->category->lang = 'fr';
    }

    public function setLength($length, $unit = 'seconds')
    {
        $this->length = new Length();
        $this->length->number = $length;
        $this->length->unit = $unit;
    }

    public function setEpisode($episodeNum, $saisonNum)
    {
        if (empty($episodeNum)) {
            return;
        }
        $this->episode = new Episode();
        $this->episode->number = $episodeNum;
        $this->episode->saison = $saisonNum;
    }

    public function setCredits($credits)
    {
        if (!empty($credits)) {
            $this->credits = urldecode($credits);
        }
    }
}

class Episode
{
    /**
     * @var integer
     */
    public $number;
    /**
     * @var string
     */
    public $saison;
}

class Credits
{
    /**
     * @var Person[]
     */
    public $directors;
    /**
     * @var Person[]
     */
    public $actors;
    /**
     * @var Person[]
     */
    public $writers;
    /**
     * @var Person[]
     */
    public $producers;
    /**
     * @var Person[]
     */
    public $presenters;
    /**
     * @var Person[]
     */
    public $commentators;
    /**
     * @var Person[]
     */
    public $guests;
}

class Category
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $lang;
}

class Length
{
    /**
     * @integer
     */
    public $number;
    /**
     * (seconds | minutes | hours)
     *
     * @var string
     */
    public $unit;
}

class Person
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $role;
}

class Video
{
    /**
     * @var boolean
     */
    public $present;
    /**
     * @var boolean
     */
    public $colour;
    /**
     * 4/3 or 16/9
     *
     * @var string
     */
    public $aspect;
    /**
     * @var string
     */
    public $quality;
}

class Audio
{
    /**
     * @var boolean
     */
    public $present;

    /**
     * 'mono','stereo','dolby','dolby digital','bilingual' and 'surround'
     *
     * @var string
     */
    public $stereo;
}
?>