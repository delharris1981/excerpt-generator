<?php

declare(strict_types=1);

namespace LuhnSummarizer;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Summarizer
 * Handles the logic for the Luhn summarization algorithm.
 */
class Summarizer
{

    private array $stop_words = [];
    private string $language = 'en';

    public function __construct(string $language = 'en')
    {
        $this->language = $language;
        $this->stop_words = $this->get_stop_words();
    }

    /**
     * Generates a summary/excerpt from the provided content.
     */
    public function summarize(string $content, int $sentence_count = 3): string
    {
        $content = wp_strip_all_tags($content);
        if (empty(trim($content))) {
            return '';
        }

        $sentences = $this->tokenize_sentences($content);
        if (count($sentences) <= $sentence_count) {
            return $content;
        }

        $significant_words = $this->identify_significant_words($content);
        $scored_sentences = [];

        foreach ($sentences as $index => $sentence) {
            $score = $this->score_sentence($sentence, $significant_words);
            $scored_sentences[] = [
                'index' => $index,
                'text' => $sentence,
                'score' => $score
            ];
        }

        // Sort by score descending
        usort($scored_sentences, fn($a, $b) => $b['score'] <=> $a['score']);

        // Take top sentences
        $top_sentences = array_slice($scored_sentences, 0, $sentence_count);

        // Re-sort by original index
        usort($top_sentences, fn($a, $b) => $a['index'] <=> $b['index']);

        return implode(' ', array_column($top_sentences, 'text'));
    }

    /**
     * Splits text into sentences.
     */
    private function tokenize_sentences(string $text): array
    {
        return preg_split('/(?<=[.?!])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Identifies significant words based on frequency.
     */
    private function identify_significant_words(string $text): array
    {
        $words = $this->tokenize_words($text);
        $filtered_words = array_filter($words, fn($w) => !in_array($w, $this->stop_words) && strlen($w) > 2);

        $frequencies = array_count_values($filtered_words);
        arsort($frequencies);

        // Significant words = Top 10% frequency
        $count = count($frequencies);
        $significant_count = max(1, (int) ceil($count * 0.1));

        return array_keys(array_slice($frequencies, 0, $significant_count));
    }

    /**
     * Tokenizes text into words.
     */
    private function tokenize_words(string $text): array
    {
        $clean = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $text), 'UTF-8');
        return preg_split('/\s+/u', $clean, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Scores a sentence based on the Luhn cluster algorithm.
     * Formula: S = k^2 / w
     */
    private function score_sentence(string $sentence, array $significant_words): float
    {
        $words = $this->tokenize_words($sentence);
        if (empty($words)) {
            return 0.0;
        }

        $sig_indices = [];
        foreach ($words as $index => $word) {
            if (in_array($word, $significant_words)) {
                $sig_indices[] = $index;
            }
        }

        if (empty($sig_indices)) {
            return 0.0;
        }

        // Identify clusters (max gap 4)
        $clusters = [];
        $current_cluster = [$sig_indices[0]];

        for ($i = 1; $i < count($sig_indices); $i++) {
            if ($sig_indices[$i] - $sig_indices[$i - 1] <= 5) { // gap is 4 words between
                $current_cluster[] = $sig_indices[$i];
            } else {
                $clusters[] = $current_cluster;
                $current_cluster = [$sig_indices[$i]];
            }
        }
        $clusters[] = $current_cluster;

        // Score each cluster and take the max
        $max_score = 0.0;
        foreach ($clusters as $cluster) {
            $k = count($cluster); // Significant words
            $w = end($cluster) - $cluster[0] + 1; // Total words in span
            $score = ($k * $k) / $w;
            if ($score > $max_score) {
                $max_score = $score;
            }
        }

        return $max_score;
    }

    /**
     * Returns a list of stop words based on the current language.
     */
    private function get_stop_words(): array
    {
        if ($this->language === 'ru') {
            return [
                'и',
                'в',
                'во',
                'не',
                'что',
                'он',
                'на',
                'я',
                'с',
                'со',
                'как',
                'а',
                'то',
                'все',
                'она',
                'так',
                'его',
                'но',
                'да',
                'ты',
                'к',
                'у',
                'же',
                'вы',
                'за',
                'бы',
                'по',
                'только',
                'ее',
                'мне',
                'было',
                'вот',
                'от',
                'меня',
                'еще',
                'нет',
                'о',
                'из',
                'ему',
                'теперь',
                'когда',
                'даже',
                'ну',
                'вдруг',
                'ли',
                'если',
                'уже',
                'или',
                'ни',
                'быть',
                'был',
                'него',
                'до',
                'вас',
                'нибудь',
                'опять',
                'уж',
                'вам',
                'ведь',
                'там',
                'потом',
                'себя',
                'ничего',
                'ей',
                'может',
                'они',
                'тут',
                'где',
                'есть',
                'надо',
                'ней',
                'для',
                'мы',
                'тебя',
                'их',
                'чем',
                'была',
                'сам',
                'чтоб',
                'без',
                'будто',
                'чего',
                'раз',
                'тоже',
                'себе',
                'под',
                'будет',
                'ж',
                'тогда',
                'кто',
                'этот',
                'того',
                'потому',
                'этого',
                'какой',
                'совсем',
                'ним',
                'здесь',
                'этом',
                'один',
                'почти',
                'мой',
                'тем',
                'чтобы',
                'нее',
                'сейчас',
                'были',
                'куда',
                'зачем',
                'всех',
                'никогда',
                'можно',
                'при',
                'наконец',
                'два',
                'об',
                'другой',
                'хоть',
                'после',
                'над',
                'больше',
                'тот',
                'через',
                'эти',
                'нас',
                'про',
                'всего',
                'них',
                'какая',
                'много',
                'разве',
                'три',
                'эту',
                'моя',
                'впрочем',
                'хорошо',
                'свою',
                'этой',
                'перед',
                'иногда',
                'лучше',
                'чуть',
                'том',
                'нельзя',
                'такой',
                'им',
                'более',
                'всегда',
                'конечно',
                'всю',
                'между'
            ];
        }

        // Default to English
        return [
            'a',
            'about',
            'above',
            'after',
            'again',
            'against',
            'all',
            'am',
            'an',
            'and',
            'any',
            'are',
            'as',
            'at',
            'be',
            'because',
            'been',
            'before',
            'being',
            'below',
            'between',
            'both',
            'but',
            'by',
            'can',
            'did',
            'do',
            'does',
            'doing',
            'down',
            'during',
            'each',
            'few',
            'for',
            'from',
            'further',
            'had',
            'has',
            'have',
            'having',
            'he',
            'her',
            'here',
            'hers',
            'herself',
            'him',
            'himself',
            'his',
            'how',
            'i',
            'if',
            'in',
            'into',
            'is',
            'it',
            'its',
            'itself',
            'just',
            'me',
            'more',
            'most',
            'my',
            'myself',
            'no',
            'nor',
            'not',
            'now',
            'of',
            'off',
            'on',
            'once',
            'only',
            'or',
            'other',
            'ought',
            'our',
            'ours',
            'ourselves',
            'out',
            'over',
            'own',
            's',
            'same',
            'she',
            'should',
            'so',
            'some',
            'such',
            't',
            'than',
            'that',
            'the',
            'their',
            'theirs',
            'them',
            'themselves',
            'then',
            'there',
            'these',
            'they',
            'this',
            'those',
            'through',
            'to',
            'too',
            'under',
            'until',
            'up',
            'very',
            'was',
            'we',
            'were',
            'what',
            'when',
            'where',
            'which',
            'while',
            'who',
            'whom',
            'why',
            'with',
            'would',
            'you',
            'your',
            'yours',
            'yourself',
            'yourselves'
        ];
    }
}
