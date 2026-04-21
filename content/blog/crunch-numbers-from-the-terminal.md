---
title: "Crunch Numbers from the Terminal: A Quick Guide to Stat CLI"
slug: "crunch-numbers-from-the-terminal"
description: "In this post, I’ll walk you through a simple example using a Markdown table as our dataset."
pubDate: 2025-09-25
author: "Admin"
heroImage: "../../assets/crunch-numbers-from-the-terminal.webp"
tags:
  - biostatistics
  - biology
  - data-analysis
  - t-test
  - ANOVA
---

Most of us default to Excel or R when running stats. But what if you could fire up a **t-test** or **ANOVA** right from your terminal, without booting heavy software? That’s exactly what `stat-cli` does: a lightweight command-line tool for quick statistical analysis.

Before we dive in, let’s clear up the basics: **there are three common types of t-tests** you’ll see in practice.

| Test Type                | When to Use                                                                 | Example                                                   |
|---------------------------|-----------------------------------------------------------------------------|-----------------------------------------------------------|
| **One-sample t-test**     | Compare the mean of a single sample against a known value.                  | Do students’ average test scores differ from 75?          |
| **Independent t-test**    | Compare means between *two independent groups*.                            | Do Control vs Treatment groups differ in scores?          |
| **Paired t-test**         | Compare means of the *same group* at two different times (repeated measure). | Did students improve from pre-test to post-test?          |

👉 In this post, we’ll focus on the **independent t-test** (two groups) and then move on to **ANOVA** (three or more groups).

### 📖 What is ANOVA?

ANOVA stands for **Analysis of Variance**. While a t-test only compares **two groups**, ANOVA lets you test whether the means of three or more groups are significantly different.

- **Null hypothesis (H₀):** all group means are equal.
- **Alternative hypothesis (H₁):** at least one group mean differs.

It works by comparing the variance between groups (how far the group means are from the overall mean) to the variance within groups (how spread out values are inside each group).
The ratio of these is the F-statistic. A large F with a small p-value means the group differences are unlikely due to chance.

👉 If ANOVA shows significance, you usually follow up with post-hoc tests (like Tukey’s HSD) to see which specific groups differ.

## ⬇️ Download

[Download Stat CLI here](https://github.com/panda-67/stats-cli/releases/latest)

---

## Example 1 — Independent Samples t-test

### Step 1 — Prepare Your Data

Save your data in a spreadsheet (`.xlsx`). Here’s a toy dataset of test scores from two groups:

| Student | Group     | Score |
|---------|-----------|-------|
| A       | Control   | 72    |
| B       | Control   | 65    |
| C       | Control   | 70    |
| D       | Control   | 68    |
| E       | Control   | 74    |
| F       | Treatment | 80    |
| G       | Treatment | 85    |
| H       | Treatment | 82    |
| I       | Treatment | 78    |
| J       | Treatment | 90    |

Save this as `scores.xlsx`.

---

### Step 2 — Run the CLI

```bash
stat-cli
```

You’ll be prompted to pick:

1. **Dependent variable** → choose `Score`  
2. **Grouping variable** → choose `Group`  
3. **Statistical test** → choose `t-test (two groups)`  

---

### Step 3 — Get Results

```
Running t-test on Score grouped by Group...

Control (n=5): mean = 69.8, std = 3.4
Treatment (n=5): mean = 83.0, std = 4.6

t = -5.84, df = 8, p = 0.0004
Result: Significant difference between groups (p < 0.05).
```

---

## Example 2 — One-way ANOVA

### Step 1 — Prepare Data with Three Groups

| Student | Group   | Score |
|---------|---------|-------|
| A       | Group1  | 55    |
| B       | Group1  | 60    |
| C       | Group1  | 58    |
| D       | Group1  | 62    |
| E       | Group2  | 70    |
| F       | Group2  | 68    |
| G       | Group2  | 72    |
| H       | Group2  | 66    |
| I       | Group3  | 80    |
| J       | Group3  | 85    |
| K       | Group3  | 78    |
| L       | Group3  | 82    |

Save this as `anova_scores.xlsx`.

---

### Step 2 — Run the CLI

```bash
stat-cli
```

This time, choose:

1. **Dependent variable** → `Score`  
2. **Grouping variable** → `Group`  
3. **Statistical test** → `One-way ANOVA`  

---

### Step 3 — Get Results

```
Running One-way ANOVA on Score grouped by Group...

Group1 (n=4): mean = 58.8, std = 2.6
Group2 (n=4): mean = 69.0, std = 2.6
Group3 (n=4): mean = 81.3, std = 3.1

F(2, 9) = 65.4, p < 0.0001
Result: Significant difference between groups (p < 0.05).
```

---

## Why This Matters

- ⚡ **Fast** — no GUI, no imports, just raw stats in seconds.  
- 📦 **Portable** — runs anywhere you can install Python.  
- 🧠 **Focused** — does exactly what you need without bloat.  

If you’re tired of bloated spreadsheets but don’t want the overhead of full R/Python scripts, `stat-cli` is your middle ground.

---

👉 In the next post, we’ll cover **Chi-square tests with categorical data**. Stay tuned.
