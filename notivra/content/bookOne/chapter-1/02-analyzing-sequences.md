---
title: "Thinking in Vectors, Not Loops"
slug: "thinking-in-vectors-not-loops"
description: "R doesn’t iterate — it transforms. Understanding vectors is the key to writing expressive, efficient, and elegant R code."
tags: ["R", "vectorization", "tidyverse", "Notivra Chronicles"]
heroImage: "../../../assets/blog-placeholder-2.jpg"
pubDate: 2025-10-16
series: "the-r-mindset-thinking-in-data"
seriesTitle: The R Mindset — Thinking in Data
order: 2
---

**Thinking in Vectors, Not Loops**

R doesn’t *iterate* — it *transforms*.  
That’s the first truth to swallow if you want to stop fighting R and start flowing with it.

---

### The Loop Mindset

Most programmers come to R from Python, C, or Java — languages that live on loops.  
You take a list, run a loop, mutate an element, and build results piece by piece.  
That mindset works there — but it breaks R’s rhythm.

```python
# Python style
numbers = [2, 3, 5, 7, 11]
squares = []
for n in numbers:
    squares.append(n**2)
print(squares)
```

In Python, you describe *steps*.  
In R, you describe *relations*.

---

### The Vector Mindset

A vector is not a loop — it’s a **container of intent**.  
When you square a vector, you’re not saying “for each element, square it.”  
You’re saying:  
> Apply this operation to the entire idea of the data.

```r
numbers <- c(2, 3, 5, 7, 11)
squares <- numbers^2
squares
```

That’s it.  
No iteration, no indexing, no mutation.  
Just **transformation** — clean, readable, fast.

---

### Why This Matters

Vectorization isn’t just about performance.  
It’s a mental shift from **how** to **what**.

When you think in vectors:

- You focus on relationships, not procedures.
- You express logic declaratively.
- Your code mirrors your reasoning — not your control flow.

That’s why R feels more like *math* than *engineering*.  
You’re describing a system of transformations, not writing machinery.

---

### Building Intuition

Let’s stretch it a bit.

```r
temps <- c(29.4, 30.1, 31.6, 33.2, 32.0)
above_31 <- temps > 31
temps[above_31]
```

This tiny example carries big weight.  
You’ve just filtered a dataset using logic as a vector — not a condition inside a loop.  
This is R’s quiet genius: logical, numeric, and character vectors are all the same kind of object, and you can use one to *filter* another.

You can’t think that way in most languages — but in R, it’s the natural order.

---

### The Tidyverse Connection

The entire **tidyverse** rests on this principle.  
Functions like `mutate()`, `filter()`, `summarize()` — they’re all vectorized transformations.  
Each one describes *what* should happen to columns of data, not *how* to iterate through them.

```r
library(dplyr)

data.frame(x = 1:5, y = 6:10) |>
  mutate(sum_xy = x + y)
```

Every column is a vector.  
Every operation is a transformation.  
That’s why tidyverse code reads like poetry — concise, declarative, expressive.

---

### Exercises

1. Create a vector of 20 random integers using `sample(1:100, 20)` and compute their cube roots without using loops.  
2. Given `scores <- c(85, 92, 77, 63, 95, 88)`, write one line to return all scores ≥ 90.  
3. In your own data analysis scripts, find one `for` loop you can rewrite as a vectorized expression.

---

> *When you think in vectors, you stop commanding data — you start conversing with it.*
