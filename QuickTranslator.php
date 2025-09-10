<?php
/**
 * *Quick Translation - based on dictionary map file
 * @author Harjeet Singh - baani.harjeet@gmail.com
 * @version 1.0
 * @since 2025-09-10
 */

class QuickTranslator {
    private $translationCache;
    private $langMapName;
    private $encoding = 'UTF-8';

    /**
     * Constructor
     * @param string $langMapName Path to the translation map file
     * @param string $encoding Character encoding (default: UTF-8)
     */
    public function __construct($langMapName, $encoding = 'UTF-8') {
        $this->translationCache = array();
        $this->langMapName = $langMapName;
        $this->encoding = $encoding;
        $this->loadDictionary($this->langMapName);
    }

    /**
     * Translates a string using translation rules from a map file
     * @param string $originalString The string to translate
     * @return string The translated string
     * @throws Exception If file cannot be read or parsed
     */
    public function translateString($originalString) {
        // Ensure string is properly encoded
        if (!mb_check_encoding($originalString, $this->encoding)) {
            $originalString = mb_convert_encoding($originalString, $this->encoding, 'auto');
        }

        // Process each translation rule
        foreach ($this->translationCache as $word => $newWord) {
            $escapedWord = preg_quote($word, '/');
            $pattern = '/(?<=^|[\s\p{P}\p{Z}])' . $escapedWord . '(?=[\s\p{P}\p{Z}]|$)/ui';
            
            //append newword to oldword with it starts with '+'
            if(str_starts_with($newWord,'+')){
                $newWord = $word . ' ' . ltrim($newWord,'+');
            }
            //prepend newword to oldword with it starts with '-'
            if(str_starts_with($newWord,'-')){
                $newWord = ltrim($newWord,'-') . ' ' . $word;
            }

            $originalString = preg_replace($pattern, $newWord, $originalString);
        }
        
        // Clean up multiple whitespaces while preserving Unicode spaces
        $originalString = preg_replace('/[\s\p{Z}]+/u', ' ', $originalString);
        $originalString = trim($originalString);
        
        return $originalString;
    }

   /**
     * Check if a word uses a writing system with clear word boundaries
     * @param string $word The word to check
     * @return bool True if word boundaries should be used
     */
    private function hasWordBoundaries($word) {
        // Check if the word contains characters from scripts that typically use word boundaries
        // These scripts generally separate words with spaces or punctuation
        $boundaryScripts = [
            '\p{Latin}',      // Latin alphabet (English, French, German, etc.)
            '\p{Cyrillic}',   // Russian, Bulgarian, Serbian, etc.
            '\p{Greek}',      // Greek
            '\p{Armenian}',   // Armenian
            '\p{Georgian}',   // Georgian
            '\p{Arabic}',     // Arabic (uses spaces)
            '\p{Hebrew}',     // Hebrew (uses spaces)
            '\p{Devanagari}', // Hindi, Sanskrit
            '\p{Bengali}',    // Bengali
            '\p{Gujarati}',   // Gujarati
            '\p{Gurmukhi}',   // Punjabi
            '\p{Kannada}',    // Kannada
            '\p{Malayalam}',  // Malayalam
            '\p{Oriya}',      // Odia
            '\p{Tamil}',      // Tamil
            '\p{Telugu}',     // Telugu
            '\p{Thai}',       // Thai (has word boundaries)
            '\p{Lao}',        // Lao
        ];
        
        $boundaryPattern = '/[' . implode('', $boundaryScripts) . ']/u';
        return preg_match($boundaryPattern, $word);
    }

    /**
     * Test if a translation would create unwanted substring matches
     * @param string $sourceWord The source word to test
     * @param string $testString A test string to check against
     * @return bool True if it would create substring matches
     */
    public function testWordBoundary($sourceWord, $testString) {
        $escapedWord = preg_quote($sourceWord, '/');
        
        if ($this->hasWordBoundaries($sourceWord)) {
            $pattern = '/(?<!\p{L}\p{M}*\p{N})' . $escapedWord . '(?!\p{L}\p{M}*\p{N})/ui';
        } else {
            $pattern = '/(?<=^|[\s\p{P}\p{Z}])' . $escapedWord . '(?=[\s\p{P}\p{Z}]|$)/u';
        }
        
        preg_match_all($pattern, $testString, $matches, PREG_OFFSET_CAPTURE);
        
        return [
            'matches' => $matches[0],
            'count' => count($matches[0]),
            'pattern' => $pattern
        ];
    }

    /**
     * Get translation cache
     * @return array The translation cache
     */
    public function getTranslationCache() {
        return $this->translationCache;
    }

    /**
     * Set translation cache
     * @param array $translationCache The translation cache to set
     */
    public function setTranslationCache($translationCache) {
        $this->translationCache = $translationCache;
    }
    
    /**
     * Get current encoding
     * @return string Current encoding
     */
    public function getEncoding() {
        return $this->encoding;
    }
    
    /**
     * Set encoding
     * @param string $encoding Character encoding to use
     */
    public function setEncoding($encoding) {
        $this->encoding = $encoding;
    }
    
    /**
     * Add a single translation rule
     * @param string $sourceWord Word to replace
     * @param string $targetWord Replacement word
     */
    public function addTranslation($sourceWord, $targetWord) {
        $this->translationCache[trim($sourceWord)] = trim($targetWord);
    }
    
    /**
     * Remove a translation rule
     * @param string $sourceWord Word to remove from translations
     */
    public function removeTranslation($sourceWord) {
        unset($this->translationCache[$sourceWord]);
    }
   
    /**
     * Load dictionary from translation map file
     * @param string $mapName Path to the translation map file
     * @throws Exception If file cannot be read or parsed
     * @return void
     */
    private function loadDictionary($mapName) {
        // Check if file exists
        if (!file_exists($mapName)) {
            throw new Exception("Translation map file '$mapName' not found.");
        }
        
        // Read the file with proper encoding handling
        $mapContent = file_get_contents($mapName);
        if ($mapContent === false) {
            throw new Exception("Unable to read translation map file '$mapName'.");
        }
        
        // Detect and convert encoding if necessary
        if (!mb_check_encoding($mapContent, $this->encoding)) {
            $detectedEncoding = mb_detect_encoding($mapContent, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
            if ($detectedEncoding) {
                $mapContent = mb_convert_encoding($mapContent, $this->encoding, $detectedEncoding);
            }
        }
        
        // Normalize line endings and split into lines
        $mapContent = str_replace(["\r\n", "\r"], "\n", $mapContent);
        $lines = array_filter(array_map('trim', explode("\n", $mapContent)));

        $dict = [];
        foreach ($lines as $lineNumber => $line) {
            // Skip comments and empty lines
            if (empty($line) || $line[0] === '#' || $line[0] === '//') {
                continue;
            }
            
            // Split by colon to get source words and target word
            $parts = explode(':', $line, 2);

            if (count($parts) < 1) {
                continue; // Skip malformed lines
            }

            $sourceWords = trim($parts[0]);
            $targetWord = isset($parts[1]) ? trim($parts[1]) : "";
            
            // Handle empty source words
            if (empty($sourceWords)) {
                continue;
            }
            
            // Split source words by comma
            $wordsToReplace = array_map('trim', explode(',', $sourceWords));
            foreach ($wordsToReplace as $word) {
                if (!empty($word)) {
                    // Normalize Unicode characters (NFC normalization)
                    $normalizedWord = class_exists('Normalizer') ? 
                        Normalizer::normalize($word, Normalizer::FORM_C) : $word;
                    $normalizedTarget = class_exists('Normalizer') ? 
                        Normalizer::normalize($targetWord, Normalizer::FORM_C) : $targetWord;
                    
                    $dict[$normalizedWord] = $normalizedTarget;
                } 
            }
        }
        
        $this->translationCache = $dict;
    }
    
    /**
     * Get statistics about the loaded dictionary
     * @return array Statistics including word count, character sets, etc.
     */
    public function getDictionaryStats() {
        $stats = [
            'word_count' => count($this->translationCache),
            'character_sets' => [],
            'avg_word_length' => 0,
            'encoding' => $this->encoding
        ];
        
        if (empty($this->translationCache)) {
            return $stats;
        }
        
        $totalLength = 0;
        $characterSets = [];
        
        foreach ($this->translationCache as $word => $translation) {
            $totalLength += mb_strlen($word, $this->encoding);
            
            // Detect character sets
            if (preg_match('/\p{Latin}/u', $word)) $characterSets['Latin'] = true;
            if (preg_match('/\p{Cyrillic}/u', $word)) $characterSets['Cyrillic'] = true;
            if (preg_match('/\p{Han}/u', $word)) $characterSets['Chinese'] = true;
            if (preg_match('/\p{Hiragana}|\p{Katakana}/u', $word)) $characterSets['Japanese'] = true;
            if (preg_match('/\p{Arabic}/u', $word)) $characterSets['Arabic'] = true;
            if (preg_match('/\p{Hebrew}/u', $word)) $characterSets['Hebrew'] = true;
            if (preg_match('/\p{Devanagari}/u', $word)) $characterSets['Devanagari'] = true;
        }
        
        $stats['avg_word_length'] = $totalLength / count($this->translationCache);
        $stats['character_sets'] = array_keys($characterSets);
        
        return $stats;
    }
}
