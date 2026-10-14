---
title: "Vectors Are Not Containers — They Are the Default"
slug: "vectors-are-not-containers-they-are-the-default"
description: "If you think of a vector as a box holding many values, you will misuse R. A vector is not a collection—it is the fundamental unit of computation."
heroImage: "../../../assets/blog-placeholder-3.jpg"
tags: ["R", "data analysis", "vectors", "Notivra Chronicles"]
pubDate: "2025-12-26"
series: "objects-structure-and-the-shape-of-thought"
seriesTitle: "Objects, Structure, and the Shape of Thought"
order: 3
---

**Vectors Are Not Containers — They Are the Default**

Most languages treat vectors as a convenience.  
R treats them as reality.

If you think of a vector as a box holding many values, you will misuse R.  
A vector is not a collection—it is the fundamental unit of computation.

>There are no scalars in R.  
>There are only vectors of length one.

This is not a quirk.  
It is the axis around which the language turns.

---

### The Scalar Illusion

```r
x <- 5
length(x)
```

The answer is not zero.  
It is not special.  
It is one.

This single fact explains why R behaves the way it does when you least expect it.

---

### Vectorized Thinking Is Not Optimization

Consider:

```r
1:5 + 10
```

Nothing is being looped explicitly.  
No instruction says “repeat.”

R assumes that operations apply to **entire vectors**.

This is not syntactic sugar.
It is a declaration of intent:

>The analyst thinks in sets, not steps.

---

### Why Loops Feel Awkward

You can write loops in R.

```r
for (i in 1:5) {
  print(i + 10)
}
```

But this is not how R wants to think.

Loops:

- hide structure
- obscure intent
- encourage state mutation
- break composability

They answer how instead of <em>what</em>.

R prefers you to state the transformation, not choreograph the movement.

---

### Recycling Rules: A Feature, Not a Trap

```r
c(1, 2, 3) + c(10, 20)
```

This line alarms beginners.  
It should.  
Recycling is powerful—and dangerous.

It exists because R assumes vector logic by default.  
It trusts the analyst to know when lengths align meaningfully.

Silence here is not negligence.  
It is expectation.

---

### Control Flow Is the Exception

This is why `if()` behaves the way it does:

```r
if (c(TRUE, FALSE)) {
  "yes"
}
```

It fails—not because R is inconsistent, but because `if()` demands a single logical value.

When you want vector logic, you must say so:

```r
ifelse(c(TRUE, FALSE), "yes", "no")
```

Explicitness is the price of power.

---

### Why This Matters for Everything That Follows

Vector-first thinking explains:

- why `dplyr` verbs operate column-wise
- why modeling functions expect vectors
- why row-wise logic is a smell
- why performance scales without effort

Once this clicks, R stops feeling slow and starts feeling inevitable.

---

R does not ask you to tell it how many times to act.  
It asks you to tell it what structure to act upon.

>Think in vectors.
>Let loops retire.
