---
title: "Tables Are Coordinated Vectors, Not Grids"
slug: "tables-are-coordinated-vectors-not-grids"
description: "A data frame is nothing more—and nothing less—than a collection of vectors that: - share the same length - are aligned by position - are interpreted together"
heroImage: "../../../assets/blog-placeholder-2.jpg"
tags: ["R", "data analysis", "tables", "Notivra Chronicles"]
pubDate: "2025-12-27"
series: "objects-structure-and-the-shape-of-thought"
seriesTitle: "Objects, Structure, and the Shape of Thought"
order: 4
---

**Tables Are Coordinated Vectors, Not Grids**

If you think of a data frame as a grid of cells, you will fight R forever.

>R does not see tables as rectangles.  
>It sees them as agreements between vectors.

A data frame is nothing more—and nothing less—than a collection of vectors that:

- share the same length
- are aligned by position
- are interpreted together

Rows are convenience.  
Columns are truth.

---

### The Illusion of Rows

Open any spreadsheet and your eyes go to rows first.  
R refuses this habit.

```r
df <- data.frame(
  species = c("A", "B", "C"),
  weight  = c(12.3, 15.8, 9.4)
)
```

Ask R what this object really is:

```r
str(df)
```

You do not see rows.  
You see named vectors.

Each column stands on its own, carrying meaning independently.  
The table exists only because these vectors agree to align.

---

### Why Row-Wise Thinking Breaks Down

When analysts ask:

>How do I loop through rows?

They are revealing a mismatch in worldview.

Row-wise logic:

- ignores vector semantics
- introduces unnecessary state
- collapses performance
- hides analytical intent

If your logic is row-wise, your structure is wrong.

Fix the structure.  
The computation will follow.

---

### Why Column-Wise Operations Feel Natural

This is why the most common R verbs act on columns:

```r
df$weight
mean(df$weight)
```

No indexing gymnastics.  
No ceremony.

A column is already the right shape for analysis.

---

### Data Frames as Contracts

A valid data frame makes a promise:

- each column represents one variable
- each row represents one observation
- each cell has a single meaning

Break this contract—by embedding multiple values in a cell, mixing units, or encoding meaning in column names—and R will not stop you.

But your analysis will quietly rot.

>R assumes discipline.  
>It does not enforce it.

---

### Why This Design Is Honest

R’s table model mirrors how statistics works.

Models do not consume rows.  
They consume vectors.

Plots do not reason about cells.  
They reason about variables.

The data frame is not a spreadsheet with ambitions.  
It is a statistical object with a human-friendly face.

---

If you treat a data frame like a grid, you will write code that looks busy and says nothing.

If you treat it as coordinated vectors,
analysis becomes alignment instead of effort.
