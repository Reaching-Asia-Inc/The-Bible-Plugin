Bible Plugin Shortcode Documentation

Basic Usage

To place a Bible verse or passage on a page or post of your WordPress site, use the [tbp-scripture] shortcode with a reference attribute.

Single Verse

[tbp-scripture reference="John 3:16"]

Verse Range

[tbp-scripture reference="John 3:16-18"]
[tbp-scripture reference="2 Timothy 3:16-17"]

Entire Chapter

[tbp-scripture reference="Psalm 23"]
[tbp-scripture reference="John 3"]

Entire Book

[tbp-scripture reference="Philemon"]
[tbp-scripture reference="Jude"]

Reference Format

The reference system is flexible and supports multiple formats:

Pattern: [Book] [Chapter]:[Verse Start]-[Verse End]

- Spaces between book name and chapter number are optional: John3:16 or John 3:16
- Different dash types are supported: -, –, —
- Extra spaces are automatically normalized

Book Names

You can use either full book names or standard abbreviations.

Old Testament Books

┌─────────────────┬──────┬──────────────────────────────────────────────────┐
│    Full Name    │ Code │                     Examples                     │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Genesis         │ GEN  │ [tbp-scripture reference="Genesis 1:1"]          │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Exodus          │ EXO  │ [tbp-scripture reference="Exodus 20:1-17"]       │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Leviticus       │ LEV  │ [tbp-scripture reference="Leviticus 19:18"]      │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Numbers         │ NUM  │ [tbp-scripture reference="Numbers 6:24-26"]      │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Deuteronomy     │ DEU  │ [tbp-scripture reference="Deuteronomy 6:4"]      │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Joshua          │ JOS  │ [tbp-scripture reference="Joshua 1:9"]           │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Judges          │ JDG  │ [tbp-scripture reference="Judges 6:12"]          │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Ruth            │ RUT  │ [tbp-scripture reference="Ruth 1:16"]            │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ 1 Samuel        │ 1SA  │ [tbp-scripture reference="1 Samuel 17:45"]       │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ 2 Samuel        │ 2SA  │ [tbp-scripture reference="2 Samuel 22:2-3"]      │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ 1 Kings         │ 1KI  │ [tbp-scripture reference="1 Kings 19:12"]        │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ 2 Kings         │ 2KI  │ [tbp-scripture reference="2 Kings 6:17"]         │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ 1 Chronicles    │ 1CH  │ [tbp-scripture reference="1 Chronicles 16:34"]   │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ 2 Chronicles    │ 2CH  │ [tbp-scripture reference="2 Chronicles 7:14"]    │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Ezra            │ EZR  │ [tbp-scripture reference="Ezra 3:11"]            │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Nehemiah        │ NEH  │ [tbp-scripture reference="Nehemiah 8:10"]        │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Esther          │ EST  │ [tbp-scripture reference="Esther 4:14"]          │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Job             │ JOB  │ [tbp-scripture reference="Job 19:25"]            │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Psalms          │ PSA  │ [tbp-scripture reference="Psalms 23:1"]          │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Proverbs        │ PRO  │ [tbp-scripture reference="Proverbs 3:5-6"]       │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Ecclesiastes    │ ECC  │ [tbp-scripture reference="Ecclesiastes 3:1"]     │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Song of Solomon │ SNG  │ [tbp-scripture reference="Song of Solomon 2:1"]  │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Isaiah          │ ISA  │ [tbp-scripture reference="Isaiah 40:31"]         │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Jeremiah        │ JER  │ [tbp-scripture reference="Jeremiah 29:11"]       │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Lamentations    │ LAM  │ [tbp-scripture reference="Lamentations 3:22-23"] │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Ezekiel         │ EZK  │ [tbp-scripture reference="Ezekiel 36:26"]        │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Daniel          │ DAN  │ [tbp-scripture reference="Daniel 3:17-18"]       │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Hosea           │ HOS  │ [tbp-scripture reference="Hosea 6:3"]            │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Joel            │ JOL  │ [tbp-scripture reference="Joel 2:28"]            │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Amos            │ AMO  │ [tbp-scripture reference="Amos 5:24"]            │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Obadiah         │ OBA  │ [tbp-scripture reference="Obadiah 1:15"]         │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Jonah           │ JON  │ [tbp-scripture reference="Jonah 2:9"]            │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Micah           │ MIC  │ [tbp-scripture reference="Micah 6:8"]            │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Nahum           │ NAM  │ [tbp-scripture reference="Nahum 1:7"]            │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Habakkuk        │ HAB  │ [tbp-scripture reference="Habakkuk 2:14"]        │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Zephaniah       │ ZEP  │ [tbp-scripture reference="Zephaniah 3:17"]       │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Haggai          │ HAG  │ [tbp-scripture reference="Haggai 2:9"]           │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Zechariah       │ ZEC  │ [tbp-scripture reference="Zechariah 4:6"]        │
├─────────────────┼──────┼──────────────────────────────────────────────────┤
│ Malachi         │ MAL  │ [tbp-scripture reference="Malachi 3:10"]         │
└─────────────────┴──────┴──────────────────────────────────────────────────┘
New Testament Books
┌─────────────────┬──────┬─────────────────────────────────────────────────────┐
│    Full Name    │ Code │                      Examples                       │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ Matthew         │ MAT  │ [tbp-scripture reference="Matthew 28:19-20"]        │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ Mark            │ MRK  │ [tbp-scripture reference="Mark 16:15"]              │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ Luke            │ LUK  │ [tbp-scripture reference="Luke 19:10"]              │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ John            │ JHN  │ [tbp-scripture reference="John 3:16"]               │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ Acts            │ ACT  │ [tbp-scripture reference="Acts 1:8"]                │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ Romans          │ ROM  │ [tbp-scripture reference="Romans 8:28"]             │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ 1 Corinthians   │ 1CO  │ [tbp-scripture reference="1 Corinthians 13:4-7"]    │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ 2 Corinthians   │ 2CO  │ [tbp-scripture reference="2 Corinthians 5:17"]      │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ Galatians       │ GAL  │ [tbp-scripture reference="Galatians 5:22-23"]       │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ Ephesians       │ EPH  │ [tbp-scripture reference="Ephesians 2:8-9"]         │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ Philippians     │ PHP  │ [tbp-scripture reference="Philippians 4:13"]        │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ Colossians      │ COL  │ [tbp-scripture reference="Colossians 3:23"]         │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ 1 Thessalonians │ 1TH  │ [tbp-scripture reference="1 Thessalonians 5:16-18"] │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ 2 Thessalonians │ 2TH  │ [tbp-scripture reference="2 Thessalonians 3:3"]     │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ 1 Timothy       │ 1TI  │ [tbp-scripture reference="1 Timothy 4:12"]          │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ 2 Timothy       │ 2TI  │ [tbp-scripture reference="2 Timothy 3:16-17"]       │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ Titus           │ TIT  │ [tbp-scripture reference="Titus 3:5"]               │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ Philemon        │ PHI  │ [tbp-scripture reference="Philemon 1:6"]            │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ Hebrews         │ HEB  │ [tbp-scripture reference="Hebrews 11:1"]            │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ James           │ JAS  │ [tbp-scripture reference="James 1:2-4"]             │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ 1 Peter         │ 1PE  │ [tbp-scripture reference="1 Peter 5:7"]             │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ 2 Peter         │ 2PE  │ [tbp-scripture reference="2 Peter 1:3"]             │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ 1 John          │ 1JN  │ [tbp-scripture reference="1 John 4:19"]             │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ 2 John          │ 2JN  │ [tbp-scripture reference="2 John 1:6"]              │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ 3 John          │ 3JN  │ [tbp-scripture reference="3 John 1:4"]              │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ Jude            │ JUD  │ [tbp-scripture reference="Jude 1:24-25"]            │
├─────────────────┼──────┼─────────────────────────────────────────────────────┤
│ Revelation      │ REV  │ [tbp-scripture reference="Revelation 21:4"]         │
└─────────────────┴──────┴─────────────────────────────────────────────────────┘
Special Notes for Numbered Books

- 1, 2 Samuel: Use 1 Samuel or 2 Samuel (or codes 1SA, 2SA)
- 1, 2 Kings: Use 1 Kings or 2 Kings (or codes 1KI, 2KI)
- 1, 2 Chronicles: Use 1 Chronicles or 2 Chronicles (or codes 1CH, 2CH)
- 1, 2 Corinthians: Use 1 Corinthians or 2 Corinthians (or codes 1CO, 2CO)
- 1, 2 Thessalonians: Use 1 Thessalonians or 2 Thessalonians (or codes 1TH, 2TH)
- 1, 2 Timothy: Use 1 Timothy or 2 Timothy (or codes 1TI, 2TI)
- 1, 2 Peter: Use 1 Peter or 2 Peter (or codes 1PE, 2PE)
- 1, 2, 3 John: Use 1 John, 2 John, or 3 John (or codes 1JN, 2JN, 3JN)

Advanced Attributes

The shortcode supports several optional attributes for customization:

Media Type

Display scripture in different formats:

[tbp-scripture reference="John 3:16" media="text"]
[tbp-scripture reference="John 3:16" media="audio"]
[tbp-scripture reference="John 3:16" media="video"]

Available media types:
- text (default) - Display text of the scripture
- audio - Display audio player for the scripture
- video - Display video for the scripture

Language

Specify the language for the scripture:

[tbp-scripture reference="John 3:16" language="eng"]
[tbp-scripture reference="John 3:16" language="spa"]

Bible Version

Specify a particular Bible translation:

[tbp-scripture reference="John 3:16" bible="ENGESV"]
[tbp-scripture reference="John 3:16" bible="ENGNIV"]

Heading Options

Control the display of scripture headings:

[tbp-scripture reference="John 3:16" heading="true"]
[tbp-scripture reference="John 3:16" heading="false"]
[tbp-scripture reference="John 3:16" heading="true" heading_text="God's Love"]

- heading - Show or hide the reference heading (default: true)
- heading_text - Custom text for the heading (overrides the default reference display)

Complete Examples

Text with Custom Heading

[tbp-scripture reference="Philippians 4:13" heading_text="Strength in Christ"]

Spanish Audio

[tbp-scripture reference="Juan 3:16" language="spa" media="audio"]

ESV Text Without Heading

[tbp-scripture reference="Psalm 23" bible="ENGESV" heading="false"]

Video with Custom Heading

[tbp-scripture reference="Matthew 28:19-20" media="video" heading_text="The Great Commission"]

Error Handling

The shortcode will display error messages if:
- An invalid media type is specified
- The reference format cannot be parsed
- No results are found for the given reference
- The specified Bible version or language is unavailable
