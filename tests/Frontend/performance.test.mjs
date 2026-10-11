import assert from 'node:assert/strict';
import { test } from 'node:test';
import { usePerformance } from '../../resources/js/composables/usePerformance.ts';

await test('Follow tracks the leader while manual navigation enters Free without moving the leader', () => {
    const session = usePerformance(6);
    session.advanceLeader();
    assert.equal(session.activeIndex.value, 1);
    session.navigate(4);
    assert.equal(session.following.value, false);
    assert.equal(session.activeIndex.value, 4);
    assert.equal(session.leaderIndex.value, 1);
    session.advanceLeader();
    assert.equal(session.activeIndex.value, 4);
    session.setFollowing(true);
    assert.equal(session.activeIndex.value, 2);
    assert.equal(session.following.value, true);
});

await test('Free freezes the currently followed section until rejoining', () => {
    const session = usePerformance(6);
    session.advanceLeader();
    session.setFollowing(false);
    session.advanceLeader();
    assert.equal(session.activeIndex.value, 1);
    session.setFollowing(true);
    assert.equal(session.activeIndex.value, 2);
});

await test('navigation rejects out of range sections and leader stops at the end', () => {
    const session = usePerformance(2);
    for (const index of [-1, 2, 0.5, NaN]) session.navigate(index);
    assert.equal(session.following.value, true);
    assert.equal(session.activeIndex.value, 0);
    session.advanceLeader();
    session.advanceLeader();
    assert.equal(session.activeIndex.value, 1);
});

await test('separate devices do not share browsing state', () => {
    const firstDevice = usePerformance(6);
    const secondDevice = usePerformance(6);
    firstDevice.navigate(3);
    assert.equal(secondDevice.following.value, true);
    assert.equal(secondDevice.activeIndex.value, 0);
});

await test('selecting Free again preserves the manually selected section', () => {
    const session = usePerformance(6);
    session.navigate(4);
    session.setFollowing(false);
    assert.equal(session.activeIndex.value, 4);
});
