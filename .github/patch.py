#!/usr/bin/env python3

import os
import rich
import subprocess
import sys
import argparse

from rich.console import Console

console = Console()

THIS_SCRIPT_DIR = os.path.dirname(os.path.realpath(__file__))
ROOT_DIR = os.path.abspath(os.path.join(THIS_SCRIPT_DIR, os.pardir))


def main():
    parser = argparse.ArgumentParser(description='Quick push to GitHub')
    parser.add_argument('-m', '--message', help='Commit message')
    parser.add_argument('-b', '--branch', help='Branch name', default='main')

    args = parser.parse_args()

    if not args.message:
        console.print('[bold red]Please provide a commit message[/bold red]')
        # prompt user for commit message
        input_message = console.input('[bold green]Enter commit message: [/bold green]')
        args.message = input_message

    # move into root directory
    os.chdir(ROOT_DIR)

    # add all files
    subprocess.run(['git', 'add', '.'])

    # commit
    subprocess.run(['git', 'commit', '-m', args.message])

    # ask if user wants to push
    push = console.input('[bold green]Push to GitHub? (y/n): [/bold green]')
    if push.lower() == 'y':
        # push
        subprocess.run(['git', 'push', 'origin', args.branch])
        console.print('[bold green]Pushed to GitHub![/bold green]')
    else:
        console.print('[bold red]Did not push to GitHub[/bold red]')
        sys.exit(0)

if __name__ == '__main__':
    main()


