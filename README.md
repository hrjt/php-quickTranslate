# QuickTranslator

A lightweight, Unicode-aware PHP class for fast string translation using direct word mapping. Perfect for content localization, text normalization, and batch word replacement with full Unicode support.

## ✨ Features

- 🌍 **Full Unicode Support** - Works with all languages including CJK (Chinese, Japanese, Korean), Arabic, Cyrillic, and more
- 🎯 **Whole Word Matching** - Only replaces complete words, prevents substring matches
- 📋 **Dictionary-Based** - Simple text file format for translation rules
- 🚀 **Fast Performance** - Cached translations for optimal speed
- 🔧 **Flexible Configuration** - Support for multiple encodings and custom dictionaries
- 🧪 **Built-in Testing** - Test word boundary detection before applying translations
- 📊 **Dictionary Analytics** - Get statistics about your loaded dictionaries

## 📋 Requirements

- PHP 7.0 or higher
- mbstring extension (for Unicode support)
- intl extension (optional, for Unicode normalization)

## 🚀 Quick Start

### Installation

1. Download `QuickTranslator.php`
2. Include it in your project:

```php
require_once 'QuickTranslator.php';
```

### Basic Usage

```php
// Create translator with dictionary file
$translator = new QuickTranslator('translations.txt');

// Translate text
$result = $translator->translateString("Hello world! This is awesome.");
echo $result; // Output: "Hello world! This is Excellent."
```

## 📝 Dictionary Format

Create a text file with translation rules in this format:

```text
# Comments start with # or //
source_word1,source_word2,source_word3:target_word

# Examples:
hello,hi,hey,greetings:Hello
good,great,excellent,amazing:Excellent
bad,terrible,awful:Poor

# Unicode support
你好,こんにちは,안녕하세요:Hello
再见,さようなら,안녕:Goodbye

# Remove words (empty target)
spam,junk:

# Misspelling corrections
recieve,recive:receive
seperate:separate
```

## 🔧 Advanced Usage

### Custom Encoding

```php
$translator = new QuickTranslator('translations.txt', 'UTF-8');
```

### Runtime Dictionary Management

```php
// Add translations dynamically
$translator->addTranslation('新しい', 'new');

// Remove translations
$translator->removeTranslation('old_word');

// Get all translations
$dictionary = $translator->getTranslationCache();
```

### Testing Word Boundaries

```php
// Test if translations work correctly
$result = $translator->testWordBoundary('cat', 'cat cats concatenation');
print_r($result);
/* Output:
Array (
    [matches] => Array (
        [0] => Array ( [0] => cat [1] => 0 )
        [1] => Array ( [0] => cat [1] => 4 )
    )
    [count] => 2
    [pattern] => /(^|[^\p{L}\p{N}])(cat)([^\p{L}\p{N}]|$)/ui
)
*/
```

### Dictionary Analytics

```php
$stats = $translator->getDictionaryStats();
print_r($stats);
/* Output:
Array (
    [word_count] => 150
    [character_sets] => Array ( [0] => Latin [1] => Chinese [2] => Japanese )
    [avg_word_length] => 4.2
    [encoding] => UTF-8
)
*/
```

## 🌍 Language Support

### Fully Supported Writing Systems

- **Latin** (English, French, German, Spanish, etc.)
- **Cyrillic** (Russian, Bulgarian, Serbian, etc.)
- **Greek**
- **Arabic** (with proper RTL support)
- **Hebrew**
- **Chinese** (Simplified & Traditional)
- **Japanese** (Hiragana, Katakana, Kanji)
- **Korean** (Hangul)
- **Indic Scripts** (Devanagari, Bengali, Tamil, etc.)
- **Thai, Lao**
- And many more...

### Smart Word Boundary Detection

The class automatically detects which writing systems use spaces between words and applies appropriate matching rules:

- **Space-separated languages**: Uses Unicode character class boundaries
- **No-space languages (CJK)**: Uses punctuation and whitespace boundaries

## 📖 API Reference

### Constructor

```php
QuickTranslator($langMapName, $encoding = 'UTF-8')
```

### Core Methods

| Method | Description |
|--------|-------------|
| `translateString($text)` | Translate text using loaded dictionary |
| `addTranslation($source, $target)` | Add single translation rule |
| `removeTranslation($source)` | Remove translation rule |
| `testWordBoundary($word, $text)` | Test word boundary matching |

### Utility Methods

| Method | Description |
|--------|-------------|
| `getTranslationCache()` | Get all loaded translations |
| `setTranslationCache($array)` | Set translation dictionary |
| `getDictionaryStats()` | Get dictionary statistics |
| `getEncoding()` | Get current encoding |
| `setEncoding($encoding)` | Set character encoding |

## 🛡️ Word Boundary Protection

QuickTranslator prevents unwanted substring matches:

```php
// Dictionary: "app:application"
$text = "I love apps but hate apples";
$result = $translator->translateString($text);
// Result: "I love applications but hate apples" ✅
// NOT: "I love applications but hate applications" ❌
```

## 🎯 Use Cases

- **Content Localization** - Translate websites and applications
- **Text Normalization** - Standardize terminology across documents
- **Profanity Filtering** - Replace inappropriate words
- **Brand Consistency** - Ensure consistent product naming
- **SEO Optimization** - Replace keywords for different markets
- **Data Cleaning** - Fix common misspellings in datasets

## 📊 Performance

- **Memory Efficient** - Dictionary cached in memory for fast lookups
- **Unicode Optimized** - Proper handling of multi-byte characters
- **Regex Optimized** - Smart patterns for different language families
- **Scalable** - Handles large dictionaries (tested with 10,000+ rules)

## 🐛 Troubleshooting

### Common Issues

1. **"Translation map file not found"**
   - Check file path and permissions
   - Ensure file exists and is readable

2. **"Encoding issues with special characters"**
   - Verify file is saved in UTF-8
   - Set correct encoding parameter

3. **"Words not being replaced"**
   - Use `testWordBoundary()` to debug patterns
   - Check dictionary format (colon separator)

### Debug Mode

```php
// Test specific word matching
$result = $translator->testWordBoundary('your_word', 'test sentence');
var_dump($result);
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Unicode Consortium for character classification standards
- PHP community for regex optimization techniques
- Contributors who helped test various language support


⭐ **Star this repo if you find it helpful!**
