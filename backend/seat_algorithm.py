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


            chosen = min(
                candidates, key=lambda cid: (neighbor_counts.get(cid, 0), -len(queues[cid]))
            )

            grid[r][c] = queues[chosen].popleft()

    return grid


def _zigzag_fill_two_courses(queues, rows):
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
    per_block_capacity = 4
    fill_ratio = filled / total_cells
    blocks_filled = num_blocks * fill_ratio
    forced_per_block = max(0, per_block_capacity - num_courses)
    return int(blocks_filled * forced_per_block * 0.5)
