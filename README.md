# 🧠 Exam-Miner 2.0

![Exam-Miner Banner](https://exam-miner.com/images/banner.png)

> **AI-Powered Exam & Quiz Generator** that automatically creates high-quality test questions from your uploaded learning materials — supporting **PDF**, **Word**, and **PowerPoint** files.

---

## 🌐 Live Demo
🎯 **Try it now:** [https://exam-miner.com](https://exam-miner.com)

---

## 🚀 About the Project
**Exam-Miner 2.0** is an intelligent, web-based platform designed to help teachers and educators generate exams and quizzes instantly.  
It uses **AI models** (Gemini, DeepSeek, OpenRouter LLMs, etc.) to analyze your uploaded files and produce **multiple exam types**, including:

- Multiple Choice  
- True or False  
- Identification  
- Matching Type  
- Essay  

It can also:
- Attach and reference **figures or diagrams** inline with questions  
- Export output to **DOCX** and **PDF**  
- Save generated exams to your personal dashboard  
- Support **JWT login** and role-based access  

---

## ✨ Key Features

| Feature | Description |
|:--------|:-------------|
| 🧩 AI Question Generation | Creates exam items based on your uploaded learning materials |
| 🖼️ Inline Figures | Automatically includes relevant images or diagrams inside the question stem |
| 📦 Multi-Format Support | Accepts PDF, DOCX, and PPTX learning materials |
| 🔐 Secure Login | JWT-based authentication (PHP backend) |
| 📄 Export Options | Download as DOCX or PDF |
| 💾 Database Saving | Save, edit, or regenerate exam sets |
| 🌙 Modern UI | Fully responsive, blue-white gradient design |
| 🧮 TOS Integration | Supports ISO 25010-aligned Table of Specifications |

---

## 🖼️ Screenshots

| Dashboard | Exam Generator | Mobile View |
|:----------:|:---------------:|:----------:|
| ![Dashboard](https://exam-miner.com/images/dashboard.png) | ![Exam Generator](https://exam-miner.com/images/generator.png) | ![Mobile View](https://exam-miner.com/images/mobile.png) |

---

## 🧰 Tech Stack

| Layer | Tools Used |
|:------|:------------|
| **Frontend** | HTML / CSS / JavaScript (Vanilla + Tailwind) |
| **Backend** | PHP 8 + MySQL |
| **AI Models** | Gemini 2.5 Flash / DeepSeek / OpenRouter LLMs |
| **PDF / DOCX Handling** | PhpWord, jsPDF, html-docx-js |
| **Auth** | Firebase PHP-JWT & local JWT tokens |
| **Hosting** | Hostinger Premium + exam-miner.com |

---

## ⚙️ Installation Guide

### 1️⃣ Clone the repository
```bash
git clone https://github.com/Itzeiaa/exam-miner-2.0.git
cd exam-miner-2.0


## ALGORITHM (pseudocode)

1) Validate session & UI boot
   - Ensure JWT, wire up buttons/inputs, set limits. 
   - Define FIG_ALLOWED = { 'Multiple Choice', 'True or False' } so only those formats can contain [[FIG:n]] tokens.  [ref] 0

2) Read inputs
   - User provides: subject/topic/grade, total question count, selected formats, TOS rows (topic + RU/AA/HOTS %), number of sets, and (optionally) selected figures.

3) Build TOS allocation
   3.1) validateTOS(): each row must sum to 100%; all topic weights ≈ 100%.  [ref] 1
   3.2) apportion(total, percents): largest-remainder rounding to integers.  [ref] 2
   3.3) allocateItemsByTOS(total):
        - Split total across topics by topicWeight using apportion.
        - For each topic, split its total into RU/AA/HOTS using apportion.  [ref] 3
   3.4) distributeAcrossFormats(count, selectedFormats, mustIncludeMatch):
        - Evenly divide a count across chosen formats with apportion.
        - If “Matching Type” was chosen but got 0, steal 1 from the largest donor format.  [ref] 4

4) Compute format totals for the whole set
   - buildFormatTotals(plan, selectedFormats):
     For each (topic × RU/AA/HOTS) bucket, call distributeAcrossFormats, and sum counts per format.
     If “Matching Type” selected but still 0, force 1 by borrowing from the largest format.  [ref] 5 6

5) Extract material + figures
   - Extract text locally (DOCX/PDF/PPTX/…).
   - Extract images/page snapshots; user chooses which figures to use.

6) Make a deterministic figure-to-question plan (so the AI cannot “pick just one”)
   - makeFigureAssignment(totalsByFormat, selectedFormats, figPlan):
     a) Keep only formats in FIG_ALLOWED.
     b) Let N = number of selected figures.
     c) Walk formats in the user’s chosen order; for each format with K items, assign figures 1..min(K, remaining) to items #1..#take for that format.
     d) Stop assigning once all N figures are placed exactly once.
     e) Return a mapping: plan[format] = [{ idx: itemIndexWithinFormat, n: figureId, desc }]  [ref] 7 8

7) Generate items per format (via the model) using TOS-aware prompts
   - For each format “fmt” with count “c”, build the prompt and request exactly c items.
   - (Generation details omitted here; this step yields raw items as text.)

8 ) Enforce the plan on the generated items (hard rule)
   - applyFigurePlanToGeneratedItems(items, fmt, plan[fmt]):
     For each item i (1-based):
       • If i is assigned a figure n: strip any existing [[FIG:*]] from the item, inject exactly one [[FIG:n]] into the STEM using injectTokenIntoStem, and ensure only that single token remains.
       • If i is NOT assigned: strip any [[FIG:*]] tokens from that item.  [ref] 9

9) Final figure-token hygiene (safety pass)
   9.1) Enforce validity & per-figure caps without reassigning:
        • Remove tokens with invalid n (not in 1..N).
        • Keep only the first token if multiple appear.
        • If a figure exceeds its per-figure quota, drop the token.  [ref] 10 11
   9.2) Normalize placement by format:
        • Multiple Choice → put [[FIG:n]] on its own line immediately before “A.” (at the end of the stem).
        • True or False → ensure [[FIG:n]] is appended at the very end of the statement.  [ref] 12 13

10) Render to HTML
    - When rendering MCQ/TF, convert [[FIG:n]] into the actual <img> using the n-th selected figure.
    - Non-allowed formats (not in FIG_ALLOWED) have tokens stripped before rendering.  [ref] 14

11) (Optional) Generate Answer Key on the finished HTML (separate step).

12) Save/export
    - Enable Save to DB and DOCX/PDF export once content exists.


## ALGORITHMS USED IN THE CODE (what they do and where)

• Largest-Remainder Integer Allocation (“apportion”)
  - Purpose: Convert percentage splits into whole-number counts while preserving totals.
  - How: floor all shares, then allocate the remaining units to the largest fractional remainders.  [ref] 15

• TOS-Driven Item Allocation (“allocateItemsByTOS”)
  - Purpose: Split the total number of questions across topics, then within each topic across RU/AA/HOTS.
  - How: Use apportion twice—first by topicWeight, then by RU/AA/HOTS.  [ref] 16

• Format Distribution with Matching-Type Guarantee (“distributeAcrossFormats”)
  - Purpose: Evenly distribute each RU/AA/HOTS bucket across selected formats.
  - Edge case: If “Matching Type” is selected but got zero, steal 1 from the largest donor format to ensure presence.  [ref] 17

• Per-Format Totals Aggregation (“buildFormatTotals”)
  - Purpose: Compute the final count to generate for each format (summing all TOS buckets) and re-apply the Matching-Type guarantee once at the end.  [ref] 18 19

• Deterministic Figure Assignment (“makeFigureAssignment”)
  - Purpose: Ensure every selected image is used exactly once and tied to a specific item index in FIG_ALLOWED formats—so the AI cannot under-use images.
  - How: Walk formats in user order; assign figures 1..N to the earl
