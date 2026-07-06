"""
Seat placement algorithm.

Goal: place students into a rows x cols grid, filling seats in reading
order (row-major, left-to-right, top-to-bottom) such that:

1. Within each course/batch, students are seated in ascending exam_roll
   order (the first roll number of that batch goes in the earliest seat
   it is assigned, and so on) -- the physical seat order must not
   scramble a batch's own roll sequence.
2. No two students from the same course are king-move adjacent
   (8-neighbor: up/down/left/right/diagonals), whenever that is
   achievable without violating (1).

Mathematical note: any 2x2 block of cells is a king-move clique of size
4, so if fewer than 4 distinct courses are present, some adjacency is
unavoidable in a full room. The roll-order constraint can force
conflicts even with 4+ courses, if one course's remaining queue empties
out while filling a run of seats. This module minimizes conflicts under
the roll-order constraint; it does not promise zero.

Algorithm: greedy fill in reading order. Each course's students are
sorted by exam_roll ascending and held in a FIFO queue. For each seat
(in row-major order), only the already-filled neighbors are the ones
above and to the left (up, upper-left, upper-right, left) -- the seats
to the right and below haven't been filled yet, so a placement can only
conflict with those four backward neighbors, and each same-course pair
is examined exactly once (from the later-filled cell's side). At each
seat, prefer a course whose next (smallest remaining) student is not
already adjacent to a same-course neighbor; among such courses, take
the one with the most students left, to spread out dominant courses.
If every remaining course conflicts, place the largest-remaining one
anyway and count the conflict honestly. This never reorders a course's
own queue, so ascending roll order within each course is preserved by
construction.

Special case -- 2 columns, exactly 2 courses: a dedicated zig-zag fill
alternates which course sits in column 0 vs column 1 on every row. This
guarantees no same-course pair is ever directly beside or above/below
each other; the only same-course adjacency that can occur is diagonal,
matching the classic zig-zag exam-seating pattern. (Some adjacency is
still unavoidable here per the 2x2-block pigeonhole above -- this just
controls *which* adjacency direction it falls on.)
"""

import re
from collections import Counter, deque
from dataclasses import dataclass, field


@dataclass
class Student:
    exam_roll: str
    name: str
    course_id: str
    course_name: str


@dataclass
class PlacementResult:
    grid: list  # rows x cols, each cell is a Student or None
    conflicts: int
    unplaced: list = field(default_factory=list)  # list[Student]


def natural_sort_key(value):
    """Sort roll numbers like '9' before '10', and 'CS9' before 'CS10'."""
    return [int(tok) if tok.isdigit() else tok.lower() for tok in re.split(r"(\d+)", str(value))]


def _neighbors(r, c, rows, cols):
    for dr in (-1, 0, 1):
        for dc in (-1, 0, 1):
            if dr == 0 and dc == 0:
                continue
            nr, nc = r + dr, c + dc
            if 0 <= nr < rows and 0 <= nc < cols:
                yield nr, nc


def _count_conflicts(grid, rows, cols):
    total = 0
    for r in range(rows):
        for c in range(cols):
            student = grid[r][c]
            if student is None:
                continue
            for nr, nc in _neighbors(r, c, rows, cols):
                if nr < r or (nr == r and nc < c):
                    continue  # count each pair once
                other = grid[nr][nc]
                if other is not None and other.course_id == student.course_id:
                    total += 1
    return total


_BACKWARD_OFFSETS = [(0, -1), (-1, -1), (-1, 0), (-1, 1)]


def _greedy_fill(queues, rows, cols):
    grid = [[None] * cols for _ in range(rows)]

    for r in range(rows):
        for c in range(cols):
            candidates = [cid for cid, q in queues.items() if q]
            if not candidates:
                continue

            neighbor_counts = Counter()
            for dr, dc in _BACKWARD_OFFSETS:
                nr, nc = r + dr, c + dc
                if 0 <= nr < rows and 0 <= nc < cols and grid[nr][nc] is not None:
                    neighbor_counts[grid[nr][nc].course_id] += 1

            # Prefer no conflict; when unavoidable, prefer the course that
            # shares the fewest backward neighbors (fewest new conflicts),
            # tie-broken by largest remaining queue to keep dominant
            # courses spread out.
            chosen = min(
                candidates, key=lambda cid: (neighbor_counts.get(cid, 0), -len(queues[cid]))
            )

            grid[r][c] = queues[chosen].popleft()

    return grid


def _zigzag_fill_two_courses(queues, rows):
    """
    Special case: exactly 2 courses filling a 2-column room. Alternating
    which course sits in column 0 vs column 1 on every row guarantees no
    same-course pair is ever directly above/below or beside each other --
    the only same-course adjacency that can occur is diagonal, which is
    the classic zig-zag exam-seating pattern and is otherwise unavoidable
    with only 2 courses (any 2x2 block is a king-move clique of size 4).
    """
    course_a, course_b = list(queues.keys())
    grid = [[None, None] for _ in range(rows)]

    for r in range(rows):
        primary, secondary = (course_a, course_b) if r % 2 == 0 else (course_b, course_a)
        for c, preferred in ((0, primary), (1, secondary)):
            other = course_b if preferred == course_a else course_a
            if queues[preferred]:
                grid[r][c] = queues[preferred].popleft()
            elif queues[other]:
                grid[r][c] = queues[other].popleft()

    return grid


def generate_seating(students, rows, cols):
    """
    students: list[Student]
    Returns PlacementResult. Deterministic (no randomness): fills seats in
    row-major reading order, always taking each course's lowest remaining
    roll number next, so batch order is preserved on the seating chart.
    """
    students_by_course = {}
    for s in students:
        students_by_course.setdefault(s.course_id, []).append(s)

    queues = {
        cid: deque(sorted(group, key=lambda s: natural_sort_key(s.exam_roll)))
        for cid, group in students_by_course.items()
    }

    if cols == 2 and len(queues) == 2:
        grid = _zigzag_fill_two_courses(queues, rows)
    else:
        grid = _greedy_fill(queues, rows, cols)

    unplaced = [student for queue in queues.values() for student in queue]
    conflicts = _count_conflicts(grid, rows, cols)

    return PlacementResult(grid=grid, conflicts=conflicts, unplaced=unplaced)


def theoretical_lower_bound(course_counts, rows, cols):
    """
    Rough lower bound on unavoidable king-move conflicts, derived from the
    2x2-block pigeonhole: each non-overlapping 2x2 block can hold at most
    one student per course without an internal conflict, so if more than
    one course's share of a block's 4 cells must repeat, conflicts are
    unavoidable. Used only to sanity-check test results, not by the
    algorithm itself. Note this bound assumes free reordering; the actual
    roll-order-preserving algorithm above may land above it, since it
    cannot reshuffle students to optimize placement.
    """
    total_students = sum(course_counts.values())
    total_cells = rows * cols
    if total_students == 0 or total_cells == 0:
        return 0

    num_courses = len([c for c in course_counts.values() if c > 0])
    if num_courses >= 4:
        return 0  # zero conflicts is achievable in principle

    num_blocks = (rows // 2) * (cols // 2)
    if num_blocks == 0:
        return 0

    filled = min(total_students, total_cells)
    # Each 2x2 block holds up to 4 students; with < 4 courses, at least
    # (cells_in_block - num_courses) same-course pairs are forced per
    # fully-filled block, in the worst case.
    per_block_capacity = 4
    fill_ratio = filled / total_cells
    blocks_filled = num_blocks * fill_ratio
    forced_per_block = max(0, per_block_capacity - num_courses)
    return int(blocks_filled * forced_per_block * 0.5)
