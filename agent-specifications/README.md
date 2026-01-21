# translator-en-us-inclusive Agent Specifications
## Executive Summary and Repository Guide

**Created:** 2025-12-23
**Version:** 1.0.0
**Status:** Ready for Implementation
**Platform:** Verumax Educational Certificate Management System

---

## Overview

This repository contains complete specifications, reference materials, and implementation guides for the **translator-en-us-inclusive** agent—a specialized translation agent designed to translate Argentine Spanish (es_AR) educational and institutional content to United States English (en_US) while implementing mandatory inclusive binary language throughout all output.

The agent is designed specifically for the Verumax platform's language localization needs, translating formal academic documents including certificates, transcripts, teacher participation records, and institutional communications.

---

## What Is This Agent?

### Agent Identity
```
Name: translator-en-us-inclusive
Version: 1.0.0
Purpose: Translate Argentine Spanish → United States English
          with mandatory inclusive binary language
Platform: Verumax Certificate Management System
```

### Core Responsibility
Translate the file `E:\appVerumax\lang\es_AR\certificatum.php` (Argentine Spanish language resource) to `E:\appVerumax\lang\en_US\certificatum.php` (United States English language resource) while implementing three non-negotiable requirements:

1. **Inclusive Binary Language** - All output explicitly recognizes and respects gender diversity through gender-neutral terminology, they/them pronouns, and rejection of generic masculine defaults

2. **Formal Academic Tone** - All translations maintain the formal, authoritative tone appropriate for institutional educational communications with no contractions or colloquialisms

3. **Structural Fidelity** - All 193 key names preserved exactly, all placeholder variables preserved exactly, all PHP syntax valid

---

## Document Structure

This specification includes 5 comprehensive documents:

### 1. **translator-en-us-inclusive-spec.json**
**Type:** Formal JSON Configuration
**Length:** ~400 lines
**Purpose:** Complete agent specification

**Contents:**
- Agent identifier and metadata
- Core instructions and quality standards
- Inclusive language dictionary (20+ term mappings)
- Quality control checklist
- Spanish-to-English translation guidelines
- Inclusive binary language strategy with 5 implementation methods
- 8 complete translation examples with analysis
- 7 common challenges and solutions
- Success criteria and deliverable format

**When to Use:**
- Need formal specification of agent behavior
- Configuring agent in system
- Reference during agent development
- Quality assurance verification

---

### 2. **INCLUSIVE-LANGUAGE-PATTERNS-EN-US.md**
**Type:** Detailed Linguistic Reference
**Length:** ~600 lines
**Purpose:** Comprehensive guide to inclusive English in academic contexts

**Sections:**
1. Core philosophy of inclusive binary language (5 principles)
2. Pronoun substitution patterns (3 patterns, 10+ examples)
3. Gender-neutral role terminology (comprehensive mapping table)
4. Compound institutional terms (7 examples)
5. Verb forms and passive voice (2 patterns)
6. Formal academic tone (sentence structure, word choices)
7. Argentine Spanish → US English cultural adaptations
8. Common translation patterns for certificate documents
9. Implementation checklist (8 items to verify per entry)
10. Challenging translation examples with solutions (5 scenarios)
11. Reference material (APA, MLA, Chicago support for singular they)
12. Quality assurance verification steps (5 comprehensive steps)
13. Emergency reference quick lookup table
14. Summary of core principles (7 key principles)

**When to Use:**
- During translation of specific entries
- Need guidance on pronoun usage
- Uncertain about formal tone requirements
- Want to understand inclusive language philosophy
- Creating training materials for human translators

---

### 3. **TRANSLATION-EXAMPLES-COMPLETE.md**
**Type:** Practical Reference Examples
**Length:** ~500 lines
**Purpose:** 33 fully-realized translation examples showing inclusive language in practice

**Sections:**
1. Page main section (2 examples)
2. Course status and terminology (4 examples)
3. Document type names (3 examples)
4. Role names (6 examples)
5. Navigation and buttons (2 examples)
6. Certificate text with placeholders (2 complex examples)
7. Teacher participation certificates (4 examples)
8. Attestations and constancies (3 examples)
9. Academic transcript (2 examples)
10. Competencies (11 term mappings)
11. Complex plural scenarios (3 examples)
12. Formal academic tone maintenance (1 example)
13. Placeholder preservation accuracy check (1 detailed example)
14. Summary of key inclusive translation principles

**When to Use:**
- Translating similar content and need concrete examples
- Need to see how to handle specific linguistic challenges
- Want to understand placeholder preservation in context
- Training example for new translators
- Quality assurance verification of similar patterns

---

### 4. **IMPLEMENTATION-GUIDE.md**
**Type:** Step-by-Step Operational Instructions
**Length:** ~700 lines
**Purpose:** Complete procedural guide for executing the translation

**Sections:**
1. Agent overview and service scope
2. Source file specification (193 key-value pairs categorized by function)
3. Target file specification (structure requirements and validation)
4. Inclusive language implementation methods (3 primary methods)
5. Terminology consistency requirements (how to maintain consistency)
6. Placeholder variable preservation (critical rules and verification)
7. Formal academic tone requirements (contractions, vocabulary, structure)
8. Quality assurance checklist (7 checkpoints with sub-items)
9. Special cases and edge cases (5 scenarios with solutions)
10. Common pitfalls to avoid (7 WRONG/RIGHT examples)
11. Testing and validation procedures (7 specific tests with code examples)
12. Submission and handoff procedures
13. Reference documentation guide
14. Support and troubleshooting
15. Final verification sign-off statement

**When to Use:**
- Executing the actual translation
- Creating test procedures
- Verifying translation quality
- Troubleshooting translation issues
- Deploying to production

---

### 5. **README.md** (this file)
**Type:** Navigation and Summary
**Purpose:** Guide to all specification documents and implementation status

---

## Quick Start Guide

### For Translators

**Step 1: Read Foundation Documents (30 minutes)**
1. Read this README.md (you are here)
2. Read Section 1 of INCLUSIVE-LANGUAGE-PATTERNS-EN-US.md (core philosophy)
3. Skim TRANSLATION-EXAMPLES-COMPLETE.md sections 1-3

**Step 2: Review Detailed Specifications (1 hour)**
1. Read entire IMPLEMENTATION-GUIDE.md sections 1-7
2. Review translator-en-us-inclusive-spec.json core instructions
3. Create your Translation Log

**Step 3: Begin Translation Process**
1. Open source file: E:\appVerumax\lang\es_AR\certificatum.php
2. Start with Section 1 (page main - 12 keys)
3. For each key-value pair:
   - Translate Spanish to English
   - Check TRANSLATION-EXAMPLES-COMPLETE.md for similar examples
   - Verify inclusive language implementation
   - Verify formal tone
   - Log role terminology choices
   - Record placeholder preservation

**Step 4: Verify and Test**
1. Upon completion, follow IMPLEMENTATION-GUIDE.md section 8 (QA Checklist)
2. Run validation tests from section 11
3. Complete final verification sign-off (section 15)
4. Deploy to E:\appVerumax\lang\en_US\certificatum.php

---

### For Quality Assurance

**QA Checklist:**

Use IMPLEMENTATION-GUIDE.md section 8 which includes:
- Pre-translation verification (7 items)
- During-translation verification (11 items per entry)
- Post-translation verification (8 major categories with 30+ sub-items)

**Critical Success Criteria (from translator-en-us-inclusive-spec.json):**
- [ ] All gendered Spanish terms → gender-neutral English equivalents
- [ ] All pronouns → inclusive alternatives (their, they, he or she)
- [ ] Formal academic tone → no contractions, professional vocabulary
- [ ] All placeholders → preserved exactly with no modifications
- [ ] All terminology → consistent throughout document
- [ ] Technical → PHP syntax valid, UTF-8 encoding, 193 keys exact match

---

### For Project Managers

**Deliverable Timeline:**
- Research & Setup: 30 minutes
- Translation Execution: 4-6 hours (depending on experience)
- Quality Assurance & Testing: 1-2 hours
- Revision & Final Verification: 30 minutes - 1 hour
- **Total Estimated Time: 6-9 hours**

**Success Criteria:**
1. ✓ All 193 keys translated to English
2. ✓ All values use inclusive binary language (verified by QA)
3. ✓ All values maintain formal academic tone (verified by QA)
4. ✓ All placeholders preserved exactly (automated verification passes)
5. ✓ PHP syntax valid (php -l command passes)
6. ✓ File importable without errors (file import test passes)
7. ✓ Key matching verified (matching verification script passes)

**Risk Mitigation:**
- Documents provide 33 complete translation examples for reference
- Detailed QA procedures minimize missed inclusive language requirements
- Automated validation tests catch structural errors
- Placeholder preservation rules clearly defined with verification procedures

---

## File Structure

```
agent-specifications/
├── README.md                          ← You are here
│
├── translator-en-us-inclusive-spec.json
│   └── Complete JSON agent specification
│       (core instructions, inclusive language rules, examples, success criteria)
│
├── INCLUSIVE-LANGUAGE-PATTERNS-EN-US.md
│   └── Detailed linguistic reference guide
│       (14 sections, pronoun rules, role terminology, formal tone)
│
├── TRANSLATION-EXAMPLES-COMPLETE.md
│   └── 33 fully-realized translation examples
│       (13 sections with practical examples and analysis)
│
├── IMPLEMENTATION-GUIDE.md
│   └── Step-by-step operational procedures
│       (15 sections, checklists, tests, validation procedures)
│
└── [Working Files - Created During Implementation]
    ├── certificatum-translation-log.txt
    │   └── Created during translation (role mappings, consistency checks)
    │
    └── E:\appVerumax\lang\en_US\certificatum.php
        └── Final deliverable (193 translated key-value pairs in PHP)
```

---

## Key Features of This Agent

### 1. Mandatory Inclusive Binary Language

**What This Means:**
- Every gendered Spanish term translated to gender-neutral English equivalent
- All pronouns use inclusive forms (their, they, he or she)
- No generic masculine defaults
- Explicit recognition of gender diversity

**Examples:**
```
docente/educador → instructor (gender-neutral role term)
sus conocimientos → their knowledge (inclusive pronoun)
aprobado/a → approved (English verb naturally neutral)
personas educadoras → educators (explicitly inclusive)
```

**Why This Matters:**
- Aligns with Title IX equity principles
- Recognized in APA 7th edition, MLA, Chicago Manual of Style
- Appropriate for institutional educational communications
- Respects all genders and gender identities

### 2. Formal Academic Tone

**What This Means:**
- No contractions (cannot, do not, is not, not can't, don't, isn't)
- Professional vocabulary (complete, submit, implement vs. finish, turn in, do)
- Passive voice where appropriate for institutional authority
- Maintains formality throughout

**Examples:**
```
WRONG: "The student can't miss class and shouldn't be late"
RIGHT: "The student cannot miss class and should not be late"

WRONG: "It's important that they're here"
RIGHT: "It is important that they are present"

WRONG: "The teacher got approved"
RIGHT: "The instructor received approval"
```

### 3. Structural Fidelity

**What This Means:**
- All 193 keys from source preserved exactly
- All 11 placeholder variables preserved exactly
- PHP syntax valid and file importable
- File structure matches source exactly

**Validation:**
- Automated: PHP syntax check
- Automated: Key count and name verification
- Automated: Placeholder count and position verification
- Manual: Spot check random 20% of entries

### 4. Comprehensive Reference Materials

**What You Get:**
- 1 formal JSON specification (configurable format)
- 1 detailed linguistic reference (600+ lines)
- 33 complete translation examples (with full analysis)
- Detailed implementation guide with tests (700+ lines)
- This executive summary and navigation guide

**Total Documentation:** ~2,500 lines of specification and guidance

---

## Translation Mapping Reference

### Role Terminology (Apply Consistently)

| Spanish | English | Context |
|---------|---------|---------|
| docente/educador | instructor | Primary teaching role |
| profesor/a | instructor/faculty member | University instruction |
| facilitador/a | facilitator | Group facilitation |
| estudiante | student | Always gender-neutral |
| orador/a | speaker | Public presentation |
| conferencista | lecturer | Academic presentation |
| tutor/a | tutor | One-on-one instruction |
| coordinador/a | coordinator | Program coordination |
| formador/a | trainer | Professional development |
| equipo docente | teaching team | Collective group |
| personas educadoras | educators | Plural emphasis |

### Pronoun Conversion (Apply Consistently)

| Spanish | English |
|---------|---------|
| su/sus | their |
| él | he / they |
| ella | she / they |
| él o ella | he or she / they |
| ellos | they |
| ellas | they |

### Placeholder Preservation (Never Modify)

| Placeholder | Meaning | Examples |
|------------|---------|----------|
| {nombre} | Full name | Appears 15 times |
| {dni} | ID number | Appears 8 times |
| {fecha} | Date | Appears 12 times |
| {carga_horaria} | Course workload | Appears 5 times |
| {institucion} | Institution name | Appears 2 times |
| {nombre_curso} | Course name | Appears 5 times |
| {rol} | Teaching role | Appears 7 times |
| {fecha_inicio} | Start date | Appears 1 time |
| {formador} | Trainer term | Appears 2 times |
| {inscripto} | Enrollment status | Appears 1 time |
| {asignado} | Assignment status | Appears 1 time |

**Total Placeholders:** 47 instances across various keys

---

## Implementation Checklist

### Pre-Implementation (Before Starting)

- [ ] Read this README.md
- [ ] Read INCLUSIVE-LANGUAGE-PATTERNS-EN-US.md sections 1-3
- [ ] Review TRANSLATION-EXAMPLES-COMPLETE.md
- [ ] Access source file: E:\appVerumax\lang\es_AR\certificatum.php
- [ ] Verify access to target directory: E:\appVerumax\lang\en_US\
- [ ] Create Translation Log document
- [ ] Review role terminology mapping above
- [ ] Review placeholder preservation rules above

### During Implementation

- [ ] Follow IMPLEMENTATION-GUIDE.md section 8 (QA Checklist)
- [ ] For each entry, verify:
  - [ ] Correct English translation
  - [ ] Inclusive language implemented
  - [ ] Formal tone maintained
  - [ ] All placeholders preserved
  - [ ] Role terminology consistent
  - [ ] Terminology recorded in log
- [ ] Maintain Translation Log with:
  - [ ] Role mappings and usage counts
  - [ ] Placeholder preservation records
  - [ ] Consistency verification notes

### Post-Implementation (Before Submission)

- [ ] Complete all items in IMPLEMENTATION-GUIDE.md section 8
- [ ] Run tests from section 11:
  - [ ] PHP syntax validation
  - [ ] File import simulation
  - [ ] Key matching verification
  - [ ] Placeholder verification
  - [ ] Inclusive language spot check
  - [ ] Formal tone verification
  - [ ] Consistency verification
- [ ] Complete final verification sign-off (section 15)
- [ ] Prepare submission package:
  - [ ] E:\appVerumax\lang\en_US\certificatum.php (deliverable)
  - [ ] Translation Log (supporting documentation)
  - [ ] Test Results (verification documentation)

---

## Success Criteria

The translation is complete and ready for deployment when:

1. **Structural Integrity**
   - [ ] All 193 keys present in target file (exact match to source)
   - [ ] All key names identical to source (no typos or modifications)
   - [ ] PHP syntax valid (php -l passes without errors)
   - [ ] File importable without errors

2. **Inclusive Language**
   - [ ] Zero instances of generic masculine language
   - [ ] All pronouns use inclusive forms (their, they)
   - [ ] All role terminology gender-neutral
   - [ ] Spot check verification (20% of entries) passes

3. **Formal Tone**
   - [ ] Zero contractions found (cannot, do not, etc.)
   - [ ] Professional vocabulary throughout
   - [ ] Institutional authority maintained
   - [ ] Sentence structure formal and clear

4. **Fidelity to Source**
   - [ ] All placeholder variables preserved exactly (47 instances verified)
   - [ ] Terminology consistent throughout
   - [ ] Meaning preserved from source
   - [ ] No Spanish text remaining (except proper nouns)

5. **Technical Validation**
   - [ ] UTF-8 encoding maintained
   - [ ] File size reasonable (similar to source)
   - [ ] All required validation tests pass
   - [ ] Final sign-off completed

---

## Quick Reference: Where to Find Everything

**Need to understand the agent's purpose?**
→ Read this README.md and translator-en-us-inclusive-spec.json

**Need guidance on inclusive language?**
→ Read INCLUSIVE-LANGUAGE-PATTERNS-EN-US.md (especially sections 2-3)

**Need examples of how to translate specific content?**
→ Read TRANSLATION-EXAMPLES-COMPLETE.md (organized by content type)

**Need step-by-step operational instructions?**
→ Read IMPLEMENTATION-GUIDE.md (sections 1-8 for procedures)

**Need quality assurance checklists?**
→ Read IMPLEMENTATION-GUIDE.md (section 8 and section 11)

**Need to troubleshoot a translation issue?**
→ Read IMPLEMENTATION-GUIDE.md (sections 9-10) or INCLUSIVE-LANGUAGE-PATTERNS-EN-US.md (section 10)

**Need formal specification for configuration?**
→ Read translator-en-us-inclusive-spec.json

---

## Support Resources

### If You're Uncertain About:

**Inclusive Language Implementation**
- Primary: INCLUSIVE-LANGUAGE-PATTERNS-EN-US.md sections 2-3
- Examples: TRANSLATION-EXAMPLES-COMPLETE.md
- Fallback: Use "their" pronouns and gender-neutral role terms

**Formal Tone Requirements**
- Primary: IMPLEMENTATION-GUIDE.md section 7
- Examples: TRANSLATION-EXAMPLES-COMPLETE.md section 12
- Fallback: Remove all contractions, use formal vocabulary

**Placeholder Preservation**
- Primary: IMPLEMENTATION-GUIDE.md section 6
- Examples: TRANSLATION-EXAMPLES-COMPLETE.md section 13
- Fallback: Never modify placeholder text; only surrounding context

**Terminology Consistency**
- Primary: IMPLEMENTATION-GUIDE.md section 5
- Reference: Role terminology mapping table above
- Tool: Create Translation Log during process

**Common Issues**
- Primary: IMPLEMENTATION-GUIDE.md sections 9-10 (special cases and pitfalls)
- Secondary: INCLUSIVE-LANGUAGE-PATTERNS-EN-US.md section 10 (challenging examples)

---

## Document Version History

| Version | Date | Status | Notes |
|---------|------|--------|-------|
| 1.0.0 | 2025-12-23 | FINAL | Complete specification, ready for implementation |

---

## Next Steps

### To Get Started Immediately:

1. **For Translators:**
   - Read INCLUSIVE-LANGUAGE-PATTERNS-EN-US.md (15 min)
   - Read IMPLEMENTATION-GUIDE.md sections 1-7 (30 min)
   - Begin translation process with section 1 of source file

2. **For QA/Verification:**
   - Read IMPLEMENTATION-GUIDE.md sections 8 and 11 (45 min)
   - Create test procedures
   - Prepare verification checklist

3. **For Project Managers:**
   - Review this README.md
   - Review IMPLEMENTATION-GUIDE.md sections 1-3 and 12
   - Estimate timeline (6-9 hours based on guidance)

---

## Contact and Questions

For questions about this specification:

**Review Documents In This Order:**
1. This README.md
2. INCLUSIVE-LANGUAGE-PATTERNS-EN-US.md (for linguistic questions)
3. IMPLEMENTATION-GUIDE.md (for procedural questions)
4. TRANSLATION-EXAMPLES-COMPLETE.md (for example clarification)
5. translator-en-us-inclusive-spec.json (for formal specification)

**For Technical Issues:**
- PHP syntax errors: See IMPLEMENTATION-GUIDE.md section 11, Test 1
- File import problems: See IMPLEMENTATION-GUIDE.md section 11, Test 2
- Validation test failures: See IMPLEMENTATION-GUIDE.md section 11

---

## License and Usage

These specifications are created specifically for the Verumax educational certificate management platform. All documents are designed for use in translating and localizing Verumax language resources.

**Document Prepared For:** Verumax Platform Language Localization
**Prepared By:** Agent Factory - Translation Agent Specialization
**Date:** 2025-12-23
**Status:** FINAL - Ready for Production Implementation

---

**Last Updated:** 2025-12-23
**Current Status:** Complete and Ready for Use
**Recommended Implementation Start Date:** Upon receipt of translator assignment
