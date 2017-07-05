<?php
include_once('Pattern.php');

class Readability
{
    /*
     * Pattern of breaking text into sentences.
     */
    const SENTENCE_PATTERN     = '/[\(\.\!\:\;\)]+/';

    /*
     * Pattern of breaking text into words.
     */
    const WORD_PATTERN         = '/[\(\.\,\!\:\;\)\?\s]+/';

    /*
     * Vowels.
     */
    const VOWELS               = 'aeiouy';

    /*
     * Entered text.
     */
    protected $text            = '';

    /*
     * Array of sentences.
     */
    protected $sentences       = [];

    /*
     * Array of words.
     */
    protected $words           = [];

    /*
     * Number of sentences.
     */
    protected $sentences_count = 0;

    /*
     * Number of words.
     */
    protected $words_count     = 0;

    /*
     * Number of syllables.
     */
    protected $syllables_count = 0;

    /*
     * Pattern class
     */
    protected $pattern;

    public function __construct()
    {
        $this->pattern = new Pattern();
    }

    /**
     * Processing of the input string, calling the counting method and outputting the result.
     *
     * @param $text
     * @return float|string
     */
    public function easeScore($text)
    {
        if (strlen($text) == 0) {
            return "Empty string";
        }

        $this->text = $text;

        try {
            $score = $this->count();
        } catch(Exception $e) {
            return $e->getMessage();
        }


        return round($score);
    }

    /**
     * Count Average Sentence Length (the number of words divided by the number of sentences).
     *
     * @return float
     * @throws Exception
     */
    protected function getASL()
    {
        if ($this->sentences_count == 0) {
            throw new Exception('A non-correct string was entered!');
        }
        $asl = $this->words_count / $this->sentences_count;

        return $asl;
    }

    /**
     * Count Average number of syllables per word (the number of syllables divided by the number of words).
     *
     * @return float
     * @throws Exception
     */
    protected function getASW()
    {
        if ($this->words_count == 0) {
            throw new Exception('A non-correct string was entered!');
        }
        $asw = $this->syllables_count / $this->words_count;

        return $asw;
    }

    /**
     * Main calculation function.
     *
     * @return float
     * @throws Exception
     */
    protected function count()
    {
        $this->sentencesCount();
        $this->wordsCount();
        $this->syllablesCount();

        $asl = $this->getASL();
        $asw = $this->getASW();

        $score = 206.835 - (1.015 * $asl) - (84.6 * $asw);

        return $score;
    }

    /**
     * Counting the number of syllables.
     */
    protected function syllablesCount()
    {
        foreach ($this->words as $word) {
            $word = $this->prefixAndSuffix($word);
            $word = $this->problemWord($word);
            $word = $this->addSyllable($word);
            $word = $this->subtractSyllable($word);
            $this->wordRemnant($word);
        }
    }

    /**
     * Selection of the prefix and suffix of a word and their calculation.
     *
     * @param $word string    current word
     * @return $word string   current word, without matching parts
     */
    protected function prefixAndSuffix($word)
    {
        foreach ($this->pattern->prefix_and_suffix_patterns as $pattern) {
            $count = 0;
            $word = preg_replace('/' . $pattern  . '/', '', $word, -1, $count);
            if ($count > 0) {
                $this->syllables_count++;
            }
        }

        return $word;
    }

    /**
     * Check if the word is relevant to the array 'problem_words', if so, its correct counting.
     *
     * @param $word string    current word
     * @return $word string   current word, without matching parts
     */
    protected function problemWord($word)
    {
        foreach ($this->pattern->problem_words as $key => $value) {
            $count = 0;
            $word = str_ireplace($key, '', $word, $count);
            if ($count > 0) {
                $this->syllables_count += $value * $count;
                break;
            }
        }

        return $word;
    }

    /**
     * Matching templates and replacing them 'add_syllable_patterns'.
     *
     * @param $word string    current word
     * @return $word string   current word, without matching parts
     */
    protected function addSyllable($word)
    {
        foreach ($this->pattern->add_syllable_patterns as $pattern) {
            $count = 0;
            $word = preg_replace('/' . $pattern . '/', '',  $word, -1, $count);
            if ($count > 0) {
                $this->syllables_count = $count * 2 + $this->syllables_count;
            }
        }

        return $word;
    }

    /**
     * Matching templates and replacing them 'subtract_syllable_patterns'.
     *
     * @param $word string    current word
     * @return $word string   current word, without matching parts
     */
    protected function subtractSyllable($word)
    {
        foreach ($this->pattern->subtract_syllable_patterns as $pattern) {
            $count = 0;
            $word = preg_replace('/' . $pattern . '/', '', $word, -1, $count);
            if ($count > 0) {
                $this->syllables_count += $count;
            }
        }

        return $word;
    }

    /**
     * Splitting the remainder of a word into syllables.
     *
     * @param $word string    current word
     */
    protected function wordRemnant($word)
    {
        for ($i = 0; $i < strlen($word); $i++) {
            if (stripos(static::VOWELS, $word[$i]) !== false) {
                $this->syllables_count++;
            }
        }
    }

    /**
     * Counting the number of sentences.
     *
     * @throws Exception
     */
    protected function sentencesCount()
    {
        $this->splitIntoSentences();

        foreach ($this->sentences as $sentence) {
            if (strlen(trim($sentence)) > 0) {
                $this->sentences_count++;
            }
        }
    }

    /**
     * Counting the number of words.
     *
     * @throws Exception
     */
    protected function wordsCount()
    {
        $this->splitIntoWords();

        foreach ($this->words as $word) {
            if (strlen(trim($word)) > 0) {
                $this->words_count++;
            }
        }
    }

    /**
     * Splitting a string into sentences.
     *
     * @throws Exception
     */
    protected function splitIntoSentences()
    {
        $this->sentences = preg_split(static::SENTENCE_PATTERN, $this->text);
        if (!$this->sentences || empty($this->sentences)) {
            throw new Exception('Error split text to sentences!');
        }
    }

    /**
     *  Splitting a line into words.
     *
     * @throws Exception
     */
    protected function splitIntoWords()
    {
        $this->words = preg_split(static::WORD_PATTERN, $this->text);
        if (!$this->words || empty($this->words)) {
            throw new Exception('Error split text to words!');
        }
    }
}
