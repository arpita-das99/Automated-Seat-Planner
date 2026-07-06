from seat_algorithm import Student, generate_seating, natural_sort_key, theoretical_lower_bound


def make_students(course_counts):
    students = []
    for course_id, count in course_counts.items():
        for i in range(count):
            students.append(
                Student(
                    exam_roll=f"{course_id}{i:03d}",
                    name=f"Student {course_id}-{i}",
                    course_id=course_id,
                    course_name=f"Course {course_id}",
                )
            )
    return students


def grid_filled_count(grid):
    return sum(1 for row in grid for cell in row if cell is not None)


def course_roll_sequence_in_reading_order(grid, rows, cols):
    """For each course, the exam_rolls in the order seats were filled
    (row-major, left-to-right, top-to-bottom) -- must be ascending."""
    sequences = {}
    for r in range(rows):
        for c in range(cols):
            cell = grid[r][c]
            if cell is not None:
                sequences.setdefault(cell.course_id, []).append(cell.exam_roll)
    return sequences


def test_balanced_four_course_fill_hits_zero_conflicts():
    rows, cols = 4, 4
    course_counts = {"A": 4, "B": 4, "C": 4, "D": 4}
    students = make_students(course_counts)

    result = generate_seating(students, rows, cols)

    assert result.conflicts == 0
    assert result.unplaced == []
    assert grid_filled_count(result.grid) == 16


def test_roll_order_preserved_per_course():
    rows, cols = 5, 5
    course_counts = {"A": 8, "B": 7, "C": 6}
    students = make_students(course_counts)

    result = generate_seating(students, rows, cols)

    sequences = course_roll_sequence_in_reading_order(result.grid, rows, cols)
    for course_id, rolls in sequences.items():
        assert rolls == sorted(rolls, key=natural_sort_key), (
            f"course {course_id} rolls out of order: {rolls}"
        )


def test_three_course_full_room_conflicts_bounded():
    rows, cols = 4, 4
    course_counts = {"A": 6, "B": 5, "C": 5}
    students = make_students(course_counts)

    result = generate_seating(students, rows, cols)
    lower_bound = theoretical_lower_bound(course_counts, rows, cols)

    assert result.unplaced == []
    # The roll-order constraint can push conflicts above the
    # free-reordering lower bound, but not wildly above it.
    assert result.conflicts >= 0
    assert result.conflicts <= lower_bound + 6


def test_one_dominant_course_stress_test_does_not_crash():
    rows, cols = 5, 5
    course_counts = {"A": 20, "B": 3, "C": 2}
    students = make_students(course_counts)

    result = generate_seating(students, rows, cols)

    assert grid_filled_count(result.grid) + len(result.unplaced) == len(students)
    assert result.conflicts >= 0


def test_room_smaller_than_student_count_keeps_lowest_rolls_and_no_crash():
    rows, cols = 3, 3  # 9 seats
    course_counts = {"A": 8, "B": 8}  # 16 students
    students = make_students(course_counts)

    result = generate_seating(students, rows, cols)

    assert grid_filled_count(result.grid) == 9
    assert len(result.unplaced) == 7

    # The unplaced students should be the highest-roll tail of each course,
    # since seating always draws each course's lowest remaining roll first.
    seated_rolls_by_course = course_roll_sequence_in_reading_order(result.grid, rows, cols)
    unplaced_rolls_by_course = {}
    for student in result.unplaced:
        unplaced_rolls_by_course.setdefault(student.course_id, []).append(student.exam_roll)

    for course_id, seated_rolls in seated_rolls_by_course.items():
        unplaced_rolls = unplaced_rolls_by_course.get(course_id, [])
        if not unplaced_rolls:
            continue
        max_seated = max(seated_rolls, key=natural_sort_key)
        min_unplaced = min(unplaced_rolls, key=natural_sort_key)
        assert natural_sort_key(max_seated) < natural_sort_key(min_unplaced)


def test_deterministic():
    rows, cols = 4, 4
    course_counts = {"A": 4, "B": 4, "C": 4, "D": 4}
    students = make_students(course_counts)

    result1 = generate_seating(students, rows, cols)
    result2 = generate_seating(students, rows, cols)

    grid1_rolls = [[c.exam_roll if c else None for c in row] for row in result1.grid]
    grid2_rolls = [[c.exam_roll if c else None for c in row] for row in result2.grid]
    assert grid1_rolls == grid2_rolls


def test_natural_sort_key_orders_numeric_rolls_correctly():
    rolls = ["10", "9", "2", "1"]
    assert sorted(rolls, key=natural_sort_key) == ["1", "2", "9", "10"]


def test_two_columns_two_courses_zigzag_no_vertical_or_horizontal_conflicts():
    rows, cols = 6, 2
    course_counts = {"A": 6, "B": 6}
    students = make_students(course_counts)

    result = generate_seating(students, rows, cols)
    grid = result.grid

    assert result.unplaced == []
    assert grid_filled_count(grid) == 12

    # Horizontal: every row must be two different courses.
    for r in range(rows):
        assert grid[r][0].course_id != grid[r][1].course_id, f"row {r} has same-course pair"

    # Vertical: same column, adjacent rows, must differ.
    for r in range(rows - 1):
        for c in range(cols):
            assert grid[r][c].course_id != grid[r + 1][c].course_id, (
                f"vertical conflict at column {c}, rows {r}/{r + 1}"
            )

    # Diagonal same-course adjacency is expected and unavoidable with only
    # 2 courses -- every diagonal pair should in fact match.
    for r in range(rows - 1):
        assert grid[r][0].course_id == grid[r + 1][1].course_id
        assert grid[r][1].course_id == grid[r + 1][0].course_id


def test_two_columns_two_courses_roll_order_preserved():
    rows, cols = 6, 2
    course_counts = {"A": 6, "B": 6}
    students = make_students(course_counts)

    result = generate_seating(students, rows, cols)

    sequences = course_roll_sequence_in_reading_order(result.grid, rows, cols)
    for course_id, rolls in sequences.items():
        assert rolls == sorted(rolls, key=natural_sort_key)


def test_two_columns_two_courses_uneven_counts_no_crash():
    rows, cols = 5, 2  # 10 seats
    course_counts = {"A": 8, "B": 2}
    students = make_students(course_counts)

    result = generate_seating(students, rows, cols)

    assert grid_filled_count(result.grid) + len(result.unplaced) == len(students)
    assert result.conflicts >= 0
