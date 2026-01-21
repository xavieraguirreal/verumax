# Implementation Guide: translator-en-us-inclusive Agent
## Complete Operational Instructions

**Document Version:** 1.0
**Created:** 2025-12-23
**Prepared For:** Agents and developers implementing translations for Verumax en_US language support

---

## 1. Agent Overview

### Agent Identifier
```
translator-en-us-inclusive
```

### Primary Purpose
Translate Argentine Spanish (es_AR) educational and institutional content to United States English (en_US) while implementing mandatory inclusive binary language throughout all output.

### Service Scope
- **Source Language:** Spanish (Argentine) - es_AR
- **Target Language:** English (United States) - en_US
- **Domain:** Educational certificates, transcripts, teacher participation records, and institutional communications
- **Platform:** Verumax academic certificate management system

### Quality Standards
- **Formal academic tone:** Required
- **Inclusive binary language:** Mandatory (non-negotiable requirement)
- **Terminology consistency:** Required across all instances
- **Placeholder preservation:** 100% - exact match required
- **PHP syntax validity:** Required

---

## 2. Source File Specification

### Source File Details
```
Path: E:\appVerumax\lang\es_AR\certificatum.php
Format: PHP return statement containing key-value array
Encoding: UTF-8
Size: 193 key-value pairs
Type: Language resource file
```

### File Structure

```php
<?php
/**
 * Traducciones Certificatum - Español Argentina (es_AR)
 * Lenguaje formal para contexto institucional/educativo
 */

return [
    'key_name' => 'value',
    'key_name_2' => 'value 2',
    // ... continues for 193 entries
];
```

### Data Categories

The 193 entries are organized into functional categories:

| Category | Key Range | Count | Examples |
|----------|-----------|-------|----------|
| Page Main | keys 1-12 | 12 | page_title, page_subtitle, search_button |
| Course List | keys 14-23 | 10 | my_courses, courses_as_student, course_status |
| Course Status | keys 25-31 | 7 | status_approved, status_in_progress |
| Document Types | keys 33-40 | 8 | doc_analyticum, doc_certificatum_approbationis |
| Teacher Roles | keys 42-49 | 8 | role_docente, role_instructor, role_facilitador |
| Navigation | keys 51-56 | 6 | back_to_trajectory, btn_print_pdf |
| Certificate Text | keys 58-77 | 20 | cert_type_trainer, cert_hereby_grants, cert_desc_trainer |
| Teacher Cert | keys 79-93 | 15 | teacher_certification, participation_certificate |
| Constancy | keys 95-109 | 15 | constancy, constancy_regular_student |
| Academic Transcript | keys 110-120 | 11 | academic_trajectory, course_timeline |
| Validation | keys 121-129 | 9 | validation_title, validation_success |
| Teacher Participation | keys 131-136 | 6 | teacher_participation, teacher_role |
| Landing Features | keys 138-143 | 6 | feature_verifiable, feature_access |
| Mis Cursos Page | keys 146-162 | 17 | my_courses_title, courses_as_student_count |
| Tabularium Page | keys 165-180 | 16 | back_to_my_courses, teacher_participation_badge |
| Competencies | keys 183-193 | 11 | competency_mediacion, competency_facilitacion |

---

## 3. Target File Specification

### Target File Details
```
Path: E:\appVerumax\lang\en_US\certificatum.php
Format: PHP return statement containing key-value array (identical structure)
Encoding: UTF-8
Expected Size: 193 key-value pairs (MUST match source exactly)
Type: Language resource file
```

### Required File Structure

```php
<?php
/**
 * Translations Certificatum - English (United States) (en_US)
 * Formal language for institutional/educational context
 * Inclusive binary language implementation: All references maintain
 * gender-neutral or paired-form inclusivity
 * Translated from Spanish (Argentine) source
 * Translation Date: [DATE OF COMPLETION]
 */

return [
    // Identical key names with translated values
    'page_title' => 'Translated value in English',
    'page_subtitle' => 'Translated value in English',
    // ... continues for exactly 193 entries
];
```

### Validation Requirements

**Before completing translation, verify:**

1. **File Structure**
   - [ ] Starts with `<?php`
   - [ ] Contains multi-line documentation header
   - [ ] Returns array with `return [`
   - [ ] All entries are key-value pairs
   - [ ] File ends with `];` followed by newline

2. **Key Count**
   - [ ] Exactly 193 keys in target file
   - [ ] Same 193 keys as source file
   - [ ] No keys removed
   - [ ] No keys added
   - [ ] No keys renamed

3. **Key Names**
   - [ ] All keys identical to source
   - [ ] No typos in key names
   - [ ] Proper formatting maintained (snake_case)
   - [ ] Exact character match to source

4. **Values**
   - [ ] All 193 values translated to English
   - [ ] All values use inclusive language
   - [ ] No Spanish text remaining (except proper nouns and institutional terms)
   - [ ] All placeholders {variable} preserved exactly

5. **Encoding**
   - [ ] UTF-8 encoding maintained
   - [ ] No encoding corruption
   - [ ] Special characters preserved correctly

6. **PHP Syntax**
   - [ ] Valid PHP syntax (test with `php -l`)
   - [ ] Proper array formatting
   - [ ] Commas after each array entry (except last)
   - [ ] No trailing commas issues

---

## 4. Inclusive Language Implementation Requirements

### Mandatory Requirement: Inclusive Binary Language

Every translation MUST implement inclusive binary language. This is not optional.

### Implementation Methods

#### Method 1: Gender-Neutral Role Terminology (PRIMARY)

Select gender-neutral English equivalents for Spanish gendered roles:

**Implementation Map:**
```
docente/educador → instructor
profesor/a → instructor / faculty member
facilitador/a → facilitator
instructor/a → instructor
estudiante → student
alumno/a → student / learner
orador/a → speaker
conferencista → lecturer
tutor/a → tutor
coordinador/a → coordinator
formador/a → trainer / training professional
personal administrativo → administrative staff
equipo docente → teaching team / faculty team
personas educadoras → educators / teaching professionals
```

**Rule:** Use the selected equivalent consistently throughout translation.

#### Method 2: Inclusive Pronouns (SECONDARY)

When pronouns must be included, use inclusive alternatives:

**Pronoun Mapping:**
```
su/sus → their (preferred: singular they)
él → he / they
ella → she / they
él o ella → he or she / they
ellos → they
ellas → they
ellos y ellas → they / they all
```

**Rule:** English "they/them" pronouns are accepted in contemporary U.S. academic English (APA 7th edition, MLA, Chicago Manual of Style).

#### Method 3: Gender-Neutral Verb Forms (AUTOMATIC)

English verbs and past participles are naturally gender-neutral:

**Examples:**
```
ha sido asignado/a → has been assigned (neutral)
se encuentra inscripto/a → is enrolled (neutral)
participó → participated (neutral)
aprobado/a → approved (neutral)
ha completado → has completed (neutral)
```

**Rule:** English naturally provides gender-neutral verb forms. No special translation needed.

### Verification Checklist

Before each translation is complete, verify:

- [ ] Every Spanish gendered term translated to gender-neutral English equivalent
- [ ] No generic masculine forms in English ("the teacher" instead of "the instructor")
- [ ] Pronouns use "their" (singular they) or "he or she" for emphasis
- [ ] All verb forms are naturally gender-neutral in English
- [ ] Formal academic tone maintained
- [ ] No contractions present (cannot, do not, etc.)
- [ ] Placeholder variables all preserved exactly

---

## 5. Terminology Consistency Requirements

### Mandatory Consistency

Once a Spanish term is assigned an English equivalent, that equivalent MUST be used consistently throughout the entire document.

### Consistency Verification

**Procedure:**

1. **Create Translation Log** at start of translation session:
   ```
   Spanish Term → English Equivalent → Count of Instances
   docente → instructor → 23
   facilitador → facilitator → 8
   orador → speaker → 4
   ```

2. **During Translation** - When encountering a term:
   - Check if already in translation log
   - Use same equivalent as previous instances
   - Record frequency

3. **Final Verification** - Search for all instances:
   - Search source file for each Spanish term
   - Count occurrences (e.g., "docente" appears 23 times)
   - Search target file for equivalent
   - Verify count matches (e.g., "instructor" should appear 23 times)
   - Document verification

**Consistency Test Examples:**

```
Source term: "docente"
Found in keys: 17, 42, 43, 44, 62, 82, 83, 85, 150, 152, etc.

Target term: "instructor"
Must appear in ALL corresponding translated values.

Verification:
- [ ] All 23+ instances of "docente" translated as "instructor" ✓
- [ ] No instance translated differently (e.g., "educator", "teacher") ✓
- [ ] No instance left in Spanish ✓
```

---

## 6. Placeholder Variable Preservation

### Critical Requirement

All placeholder variables MUST be preserved exactly as they appear in source. This is non-negotiable.

### Identified Placeholders

```
{nombre}          → Full name of person
{dni}             → National ID number
{fecha}           → Date
{carga_horaria}   → Course workload in hours
{institucion}     → Institution name
{nombre_curso}    → Course name
{rol}             → Teaching role
{fecha_inicio}    → Course start date
{formador}        → Trainer/educator term
{inscripto}       → Enrollment status
{asignado}        → Assignment status
```

### Preservation Rules

1. **Never modify placeholder content**
   - ❌ {fecha} → {date} (WRONG - modified)
   - ✓ {fecha} → {fecha} (CORRECT - preserved)

2. **Never remove placeholders**
   - ❌ "On the date it is certified" (WRONG - removed {fecha})
   - ✓ "On {fecha}, it is hereby certified" (CORRECT - preserved)

3. **Never move placeholders to different position**
   - ❌ "{nombre} on {fecha}" when source is "On {fecha}, {nombre}" (WRONG)
   - ✓ "On {fecha}, {nombre}" (CORRECT)

4. **Text around placeholders may change**
   - ✓ Text before/after can be adapted for English grammar
   - ✓ Placeholders themselves remain untouched
   - ✓ Context adjustments for English syntax are acceptable

### Placeholder Count Verification

**Procedure:**

1. Extract all placeholders from source file
2. Count total occurrences of each
3. Translate surroundings, preserve placeholders
4. Count placeholders in target file
5. Verify counts match exactly

**Example Verification:**

```
Source File Count:
{fecha} appears 12 times
{nombre} appears 15 times
{dni} appears 8 times
{carga_horaria} appears 5 times
{rol} appears 7 times
Total: 47 placeholders

Target File Count (must match):
{fecha} must appear 12 times
{nombre} must appear 15 times
{dni} must appear 8 times
{carga_horaria} must appear 5 times
{rol} must appear 7 times
Total: 47 placeholders

Status: ✓ VERIFIED (counts match)
```

---

## 7. Formal Academic Tone Requirements

### Non-Negotiable Formality Standards

#### No Contractions Allowed

**Prohibited:**
```
can't, don't, won't, hasn't, isn't, it's, that's, you're, they're,
there's, he's, she's, we've, they've, I'm, you'll, we'll, would've,
could've, should've, etc.
```

**Required Alternatives:**
```
cannot, do not, will not, has not, is not, it is, that is, you are,
they are, there is, he is, she is, we have, they have, I am, you will,
we will, would have, could have, should have, etc.
```

#### Formal Vocabulary Selection

| Informal/Casual | Formal/Academic |
|-----------------|-----------------|
| finish | complete / conclude |
| get | receive / obtain |
| okay | approved / acceptable |
| help | assist / provide support |
| tell | inform / communicate |
| give | provide / deliver / grant |
| say | state / affirm / declare |
| show | demonstrate / illustrate |
| make | create / develop / establish |
| try | attempt / endeavor |
| think | consider / regard |
| know | recognize / understand |
| use | utilize / implement / employ |

#### Sentence Structure for Formality

**Less Formal:**
```
The student got approved in the course.
```

**More Formal:**
```
The student received approval in the course.
```

**Most Formal (Institutional):**
```
Upon successful completion, the student receives formal approval.
```

### Institutional Phrasing Examples

| Context | Formal Phrase | Institutional Phrase |
|---------|---------------|-------------------|
| Document issuance | It is stated that | It is hereby certified that |
| Proof/attestation | This shows that | By this document, it is attested that |
| Document extension | This document is issued | The present is extended |
| Closing statement | For all purposes needed | For all purposes deemed appropriate |
| Approval recognition | The person approved | The individual has received approval |
| Completion status | Finished the course | Successfully completed the course |

---

## 8. Quality Assurance Checklist

### Pre-Translation Review

- [ ] Source file accessible at E:\appVerumax\lang\es_AR\certificatum.php
- [ ] Source file readable and properly formatted
- [ ] All 193 keys identified and counted
- [ ] All placeholder variables identified and logged
- [ ] Inclusive language patterns reviewed
- [ ] Role terminology mapping created and documented
- [ ] Formal academic tone guidelines understood

### During-Translation Verification

**For each key-value pair, verify:**

- [ ] Spanish value fully understood in context
- [ ] Role terminology selected and recorded
- [ ] No gendered language defaults used
- [ ] All pronouns are inclusive (their, they, he or she)
- [ ] All placeholders preserved exactly
- [ ] No contractions used
- [ ] Formal tone appropriate for institutional context
- [ ] Terminology consistent with translation log
- [ ] Natural English flow while maintaining formality

### Post-Translation Verification

**Final comprehensive check:**

1. **Count Verification**
   - [ ] Source file: 193 keys
   - [ ] Target file: 193 keys
   - [ ] Count match: YES

2. **Key Verification**
   - [ ] All 193 keys from source present in target
   - [ ] No extra keys added
   - [ ] No keys removed
   - [ ] Key names exactly match source

3. **Translation Verification**
   - [ ] All 193 values translated to English
   - [ ] No Spanish text remaining (except proper nouns)
   - [ ] All values use formal academic tone
   - [ ] All values implement inclusive language

4. **Placeholder Verification**
   - [ ] All source placeholders preserved in target
   - [ ] No placeholders removed
   - [ ] No placeholders modified
   - [ ] Placeholder counts match

5. **Language Verification**
   - [ ] Zero generic masculine forms remaining
   - [ ] All pronouns are inclusive
   - [ ] No contractions present
   - [ ] Formal vocabulary throughout

6. **Terminology Verification**
   - [ ] Role terminology consistent throughout
   - [ ] Translation log verified against final file
   - [ ] No inconsistent translations of same term
   - [ ] All 11 competency terms translated

7. **Technical Verification**
   - [ ] UTF-8 encoding maintained
   - [ ] PHP syntax valid (php -l passes)
   - [ ] File can be imported without errors
   - [ ] Array structure properly formatted

8. **Spot Check Verification**
   - [ ] Randomly select 20% of entries (39 entries)
   - [ ] Verify each for:
     - [ ] Correct translation meaning
     - [ ] Inclusive language implementation
     - [ ] Formal tone
     - [ ] Placeholder preservation
     - [ ] Grammatical correctness

---

## 9. Special Cases and Edge Cases

### Case 1: Gendered Spanish Terms with No Direct English Equivalent

**Situation:** Spanish has gendered term (docente/educador) with grammatical gender distinction.

**English Problem:** English often has single gender-neutral term.

**Solution:** Use gender-neutral English term that encompasses both Spanish forms.

**Example:**
```
Spanish: "Docente e Instructor" (attempting to show role diversity + gender)
English: "Instructor" or "Educator" (single gender-neutral term covers both)
```

---

### Case 2: Institutional Terms That Should Be Preserved

**Situation:** Some Spanish terms are institution-specific (DNI, carga horaria).

**English Approach:** Keep term as-is if widely understood in US academic context, or provide English equivalent.

**Examples:**
```
DNI → Keep as "DNI" in formal institutional context (may add "National ID" as clarification)
carga horaria → Translate to "workload" or "course hours" (context-dependent)
formación → Translate to "training" or "educational program"
cursada → Not an exact English term; translate as "course" or "coursework"
```

---

### Case 3: Role-Specific Terminology with Context

**Situation:** Same Spanish role term might appear in different contexts requiring slightly different English terms.

**Solution:** Choose primary English equivalent and use consistently. Variations in surrounding context okay.

**Examples:**
```
"Docente" in title context → "Instructor" or "Teaching Professional"
"Docente" in general context → "instructor"
"Docente" in formal statement → "instructor" (same throughout)

Consistency rule: SAME Spanish term = SAME English term throughout
```

---

### Case 4: Plural Collective References

**Situation:** Spanish might specify gender with collective term: "personas educadoras", "equipo docente".

**Solution:** Use gender-neutral or explicitly inclusive English compound.

**Examples:**
```
"personas educadoras" → "educators" or "teaching professionals"
"equipo docente" → "teaching team" or "faculty team"
"docentes e instructores" → "instructors and facilitators" or "teaching professionals"
```

---

### Case 5: Placeholder Content That Might Be Gendered

**Situation:** Placeholders like {formador}, {inscripto}, {asignado} might be filled with gendered content.

**Solution:** Preserve placeholder exactly. English surrounding text should be gender-neutral.

**Example:**
```
Spanish: "ha sido {asignado} como {rol}"
English: "has been assigned as a {rol}"

Even though Spanish {asignado} might be filled with feminine "asignada",
the English "assigned" works for all genders.
```

---

## 10. Common Pitfalls to Avoid

### Pitfall 1: Using Generic Masculine in English

**WRONG:**
```
"The teacher must submit his report."
"Each instructor brings his expertise."
```

**RIGHT:**
```
"The instructor must submit their report."
"Each instructor brings their expertise."
```

---

### Pitfall 2: Inconsistent Role Terminology

**WRONG (Inconsistent):**
```
Key 1: "docente" → "teacher"
Key 2: "docente" → "educator"
Key 3: "docente" → "instructor"
```

**RIGHT (Consistent):**
```
Key 1: "docente" → "instructor"
Key 2: "docente" → "instructor"
Key 3: "docente" → "instructor"
```

---

### Pitfall 3: Modifying Placeholders

**WRONG:**
```
Spanish: "El día {fecha} se certifica"
English: "On the [date] it is certified"  ← Placeholder removed!
```

**RIGHT:**
```
Spanish: "El día {fecha} se certifica"
English: "On {fecha}, it is hereby certified"  ← Placeholder preserved
```

---

### Pitfall 4: Using Contractions in Formal Text

**WRONG:**
```
"The student can't miss class and shouldn't be late."
"It's important that they're present."
```

**RIGHT:**
```
"The student cannot miss class and should not be late."
"It is important that they are present."
```

---

### Pitfall 5: Losing Context in Translation

**WRONG:**
```
Spanish: "Ha completado y aprobado el curso"
English: "Has completed the course"  ← "aprobado" (approved) missing!
```

**RIGHT:**
```
Spanish: "Ha completado y aprobado el curso"
English: "Has successfully completed and approved the course"
```

---

### Pitfall 6: Translating Proper Nouns

**WRONG:**
```
Spanish: "Sociedad Argentina de Justicia Restaurativa"
English: "Argentine Society of Restorative Justice"  ← Translated proper name!
```

**RIGHT:**
```
Spanish: "Sociedad Argentina de Justicia Restaurativa"
English: "Sociedad Argentina de Justicia Restaurativa"  ← Keep as is
```

---

### Pitfall 7: Inconsistent Placeholder Preservation

**WRONG:**
```
Spanish: "El {nombre} completa {carga_horaria} horas"
English: "The person {nombre} completes {hours} hours"  ← {carga_horaria} changed to {hours}!
```

**RIGHT:**
```
Spanish: "El {nombre} completa {carga_horaria} horas"
English: "{nombre} completes {carga_horaria} hours"
```

---

## 11. Testing and Validation Procedures

### Test 1: PHP Syntax Validation

**Procedure:**
```bash
php -l E:\appVerumax\lang\en_US\certificatum.php
```

**Expected Output:**
```
No syntax errors detected in /path/to/certificatum.php
```

**If Errors Appear:**
- Identify line number in error message
- Check array syntax at that location
- Fix formatting (missing commas, quotes, etc.)
- Re-test until validation passes

---

### Test 2: File Import Simulation

**Procedure:**
Create test PHP file:
```php
<?php
$strings = include('E:\appVerumax\lang\en_US\certificatum.php');
echo "File loaded successfully. Key count: " . count($strings) . "\n";
if (count($strings) === 193) {
    echo "✓ Correct key count (193)\n";
} else {
    echo "✗ Wrong key count. Expected 193, got " . count($strings) . "\n";
}
?>
```

**Expected Output:**
```
File loaded successfully. Key count: 193
✓ Correct key count (193)
```

---

### Test 3: Key Matching Verification

**Procedure:**
Create comparison script:
```php
<?php
$source = include('E:\appVerumax\lang\es_AR\certificatum.php');
$target = include('E:\appVerumax\lang\en_US\certificatum.php');

$source_keys = array_keys($source);
$target_keys = array_keys($target);

$missing = array_diff($source_keys, $target_keys);
$extra = array_diff($target_keys, $source_keys);

if (empty($missing) && empty($extra)) {
    echo "✓ All keys match perfectly\n";
} else {
    if (!empty($missing)) echo "Missing keys: " . implode(", ", $missing) . "\n";
    if (!empty($extra)) echo "Extra keys: " . implode(", ", $extra) . "\n";
}
?>
```

**Expected Output:**
```
✓ All keys match perfectly
```

---

### Test 4: Placeholder Verification

**Procedure:**
Create placeholder extraction script:
```php
<?php
$target = include('E:\appVerumax\lang\en_US\certificatum.php');

$placeholders = [
    '{nombre}' => 0,
    '{dni}' => 0,
    '{fecha}' => 0,
    '{carga_horaria}' => 0,
    '{institucion}' => 0,
    '{nombre_curso}' => 0,
    '{rol}' => 0,
    '{fecha_inicio}' => 0,
    '{formador}' => 0,
    '{inscripto}' => 0,
    '{asignado}' => 0,
];

foreach ($target as $value) {
    foreach (array_keys($placeholders) as $placeholder) {
        $placeholders[$placeholder] += substr_count($value, $placeholder);
    }
}

foreach ($placeholders as $placeholder => $count) {
    echo "$placeholder: $count instances\n";
}
?>
```

**Expected Output:**
```
{nombre}: 15 instances
{dni}: 8 instances
{fecha}: 12 instances
{carga_horaria}: 5 instances
{institucion}: 2 instances
{nombre_curso}: 5 instances
{rol}: 7 instances
{fecha_inicio}: 1 instance
{formador}: 2 instances
{inscripto}: 1 instance
{asignado}: 1 instance
```

**Note:** Exact counts depend on source file; numbers above are examples.

---

### Test 5: Inclusive Language Spot Check

**Procedure:**
1. Search target file for these terms: "his" "him" "he " "she " "her "
2. For each match, verify:
   - Is it truly gender-specific (proper error)?
   - Or acceptable in context (e.g., "herself" as reflexive)?
3. Replace gender-specific pronouns with "their" or "they"

**Expected Result:**
- Minimal instances of gender-specific pronouns
- Any present are in appropriate institutional context
- No generic masculine defaults ("his" standing alone)

---

### Test 6: Formal Tone Verification

**Procedure:**
Search target file for these terms:
```
can't, don't, won't, isn't, can't, shouldn't, doesn't, haven't,
won't, couldn't, wouldn't, didn't, wasn't, weren't, it's, that's,
you're, they're, we're, he's, she's, I'm, let's, we've, I've, etc.
```

**Expected Result:**
- ZERO contractions found
- Any apostrophes should be in proper nouns only (e.g., "D'Angelo", "D.N.I.")

---

### Test 7: Consistency Verification

**Procedure:**
1. For each role term in mapping, search target file
2. Count occurrences of each English equivalent
3. Verify no alternative translations of same term exist

**Example:**
```
Search for "docente" occurrences in source: 23 times
Search for "instructor" in target: Should be 23+ times (may appear in other contexts)
Search for "educator" in target for docente translations: Should be 0 (if not selected)
Search for "teacher" in target for docente translations: Should be 0 (if not selected)
```

---

## 12. Submission and Handoff

### Deliverable Files

The translation process produces one primary deliverable:

**File 1: E:\appVerumax\lang\en_US\certificatum.php**
- Translated language resource file
- All 193 keys and values
- PHP format matching source structure
- Ready for production deployment

### Supplementary Documentation (Optional)

The following files support the translation and should be preserved:

**File 2: Translation Log** (create during process)
```
Date: [completion date]
Source: certificatum.php (es_AR)
Target: certificatum.php (en_US)

Role Terminology Mapping:
- docente → instructor (23 instances verified)
- facilitador → facilitator (8 instances verified)
- [continue for all role terms]

Placeholder Count Verification:
- {nombre}: [count] instances verified
- {dni}: [count] instances verified
- [continue for all placeholders]

Testing Results:
- PHP Syntax: PASS
- Key Count: 193 ✓
- Key Names: MATCH ✓
- Placeholders: MATCH ✓
- Inclusive Language: VERIFIED ✓
- Formal Tone: VERIFIED ✓
- Consistency: VERIFIED ✓
```

### Pre-Deployment Checklist

Before deploying translated file to production:

- [ ] File located at: E:\appVerumax\lang\en_US\certificatum.php
- [ ] PHP syntax validated with: php -l
- [ ] All 193 keys present
- [ ] All values in English (no Spanish remaining)
- [ ] All values use inclusive language (verified with spot checks)
- [ ] All values use formal tone (no contractions)
- [ ] All placeholders preserved exactly
- [ ] File encoding is UTF-8
- [ ] File structure matches source file structure
- [ ] File readable by PHP include() function
- [ ] Backup of previous version created (if exists)

---

## 13. Reference Documentation

The following reference documents are available to support translation:

### Document 1: translator-en-us-inclusive-spec.json
**Purpose:** Complete agent configuration specification
**Contents:** Agent purpose, instructions, quality standards, inclusive language strategy
**Use When:** Need complete formal specification of agent behavior

### Document 2: INCLUSIVE-LANGUAGE-PATTERNS-EN-US.md
**Purpose:** Detailed linguistic patterns for inclusive English
**Contents:** Pronoun rules, role terminology, formal tone requirements, examples
**Use When:** Translating and need specific linguistic guidance

### Document 3: TRANSLATION-EXAMPLES-COMPLETE.md
**Purpose:** 33 fully-realized translation examples with analysis
**Contents:** Complete Spanish-to-English translations showing inclusive language implementation
**Use When:** Translating similar content and need reference examples

### Document 4: IMPLEMENTATION-GUIDE.md (this document)
**Purpose:** Step-by-step operational instructions
**Contents:** File specifications, checklists, testing procedures, submission guidelines
**Use When:** Executing translation and need procedural guidance

---

## 14. Support and Troubleshooting

### Common Issues and Solutions

**Issue 1: Can't decide between multiple English equivalents for a Spanish term**
- **Solution:** Review TRANSLATION-EXAMPLES-COMPLETE.md for precedent
- **Fallback:** Choose most common U.S. academic term and apply consistently

**Issue 2: Uncertain about inclusive language implementation**
- **Solution:** Review INCLUSIVE-LANGUAGE-PATTERNS-EN-US.md (Section 2-3)
- **Fallback:** Use "their" for pronouns, gender-neutral role terms

**Issue 3: Placeholder preservation confusion**
- **Solution:** Review IMPLEMENTATION-GUIDE.md (Section 6)
- **Fallback:** Never modify placeholder content; only text around it

**Issue 4: Formal tone concerns**
- **Solution:** Review IMPLEMENTATION-GUIDE.md (Section 7)
- **Fallback:** Remove all contractions, check for formal vocabulary

**Issue 5: Consistency problems with role terms**
- **Solution:** Review IMPLEMENTATION-GUIDE.md (Section 5)
- **Fallback:** Create translation log, search-and-replace for consistency

---

## 15. Quality Assurance Sign-Off

### Final Verification Statement

Upon completion of translation, the responsible party should verify:

```
TRANSLATION COMPLETION VERIFICATION

Source File: E:\appVerumax\lang\es_AR\certificatum.php
Target File: E:\appVerumax\lang\en_US\certificatum.php
Completion Date: [DATE]

I certify that:

[ ] All 193 keys from source file are present in target file
[ ] All 193 values have been translated to English
[ ] All translations implement inclusive binary language
[ ] All translations maintain formal academic tone
[ ] All placeholder variables have been preserved exactly
[ ] All terminology has been applied consistently
[ ] PHP syntax has been validated
[ ] File encoding is UTF-8
[ ] File can be imported without errors
[ ] Spot check verification completed (20% of entries)

Verified By: [NAME/AGENT ID]
Verification Date: [DATE]
Status: READY FOR PRODUCTION
```

---

**Document Prepared For:** translator-en-us-inclusive Agent Implementation
**Last Updated:** 2025-12-23
**Status:** FINAL - Ready for Production Implementation
**Authorized For:** Verumax Platform Language File Deployment
